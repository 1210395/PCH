<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRequest;
use App\Models\Designer;
use App\Models\Notification;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    /**
     * Show all conversations list
     */
    public function index($locale, Request $request)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return redirect()->route('login', ['locale' => $locale]);
        }

        $query = Conversation::where(function ($q) use ($currentDesigner) {
            $q->where('designer_1_id', $currentDesigner->id)
              ->orWhere('designer_2_id', $currentDesigner->id);
        })
        ->with(['designer1', 'designer2', 'lastMessage']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strip_tags($request->search);

            $query->where(function ($q) use ($searchTerm, $currentDesigner) {
                $q->whereHas('designer1', function ($designerQuery) use ($searchTerm, $currentDesigner) {
                    $designerQuery->where('name', 'like', '%' . $searchTerm . '%')
                                  ->where('id', '!=', $currentDesigner->id);
                })
                ->orWhereHas('designer2', function ($designerQuery) use ($searchTerm, $currentDesigner) {
                    $designerQuery->where('name', 'like', '%' . $searchTerm . '%')
                                  ->where('id', '!=', $currentDesigner->id);
                });
            });
        }

        // Filter by unread
        if ($request->has('filter') && $request->filter === 'unread') {
            $query->where(function ($q) use ($currentDesigner) {
                $q->where(function ($subQ) use ($currentDesigner) {
                    $subQ->where('designer_1_id', $currentDesigner->id)
                         ->where('designer_1_unread_count', '>', 0);
                })
                ->orWhere(function ($subQ) use ($currentDesigner) {
                    $subQ->where('designer_2_id', $currentDesigner->id)
                         ->where('designer_2_unread_count', '>', 0);
                });
            });
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->get();

        return view('messages.index', [
            'conversations' => $conversations,
            'searchTerm' => $request->search ?? '',
            'currentFilter' => $request->filter ?? 'all'
        ]);
    }

    /**
     * Show message composer for a specific designer
     */
    public function compose($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('error', 'Please login to send messages');
        }

        if ($currentDesigner->id == $designerId) {
            return redirect()->back()->with('error', 'You cannot message yourself');
        }

        $recipient = Designer::findOrFail($designerId);

        if (!$recipient->allow_messages) {
            return redirect()->back()->with('error', 'This designer is not accepting messages');
        }

        // If conversation exists, redirect to chat
        $conversation = $this->findConversation($currentDesigner->id, $designerId);
        if ($conversation) {
            return redirect()->route('messages.chat', [
                'locale' => $locale,
                'designerId' => $designerId
            ]);
        }

        $existingRequest = MessageRequest::where('from_designer_id', $currentDesigner->id)
            ->where('to_designer_id', $designerId)
            ->where('status', 'pending')
            ->first();

        return view('messages.compose', [
            'recipient' => $recipient,
            'existingRequest' => $existingRequest
        ]);
    }

    /**
     * Send a new message or message request
     */
    public function send(Request $request, $locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $recipient = Designer::findOrFail($designerId);

        if (!$recipient->allow_messages) {
            return response()->json([
                'success' => false,
                'message' => 'This designer is not accepting messages'
            ], 403);
        }

        $conversation = $this->findConversation($currentDesigner->id, $designerId);

        if ($conversation) {
            $this->createMessageInConversation($conversation, $currentDesigner->id, $designerId, $validated['message']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'redirect' => route('messages.chat', ['locale' => $locale, 'designerId' => $designerId])
            ]);
        }

        // No conversation exists - create message request
        MessageRequest::create([
            'from_designer_id' => $currentDesigner->id,
            'to_designer_id' => $designerId,
            'message' => strip_tags($validated['message']),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message request sent successfully',
            'redirect' => route('messages.requests', ['locale' => $locale])
        ]);
    }

    /**
     * Show live chat interface with a designer
     */
    public function chat($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('error', 'Please login to chat');
        }

        $otherDesigner = Designer::findOrFail($designerId);
        $conversation = Conversation::findOrCreateBetween($currentDesigner->id, $designerId);
        $conversation->markAsRead($currentDesigner->id);

        $messages = $conversation->messages()
            ->with('sender', 'receiver')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('messages.chat', [
            'conversation' => $conversation,
            'otherDesigner' => $otherDesigner,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message in an existing conversation (AJAX)
     */
    public function sendInChat(Request $request, $locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        if (!$this->isParticipant($conversation, $currentDesigner->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $receiverId = $this->getReceiverId($conversation, $currentDesigner->id);

        $message = $this->createMessageInConversation($conversation, $currentDesigner->id, $receiverId, $validated['message']);
        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get new messages (AJAX polling)
     */
    public function getMessages($locale, $conversationId, Request $request)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        if (!$this->isParticipant($conversation, $currentDesigner->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $lastMessageId = $request->input('last_message_id', 0);

        $messages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $conversation->markAsRead($currentDesigner->id);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Fetch all messages for a conversation (for chat panel)
     */
    public function fetchMessages($locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        if (!$this->isParticipant($conversation, $currentDesigner->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Mark messages as read in a conversation (for chat panel)
     */
    public function markAsRead($locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        if (!$this->isParticipant($conversation, $currentDesigner->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation->markAsRead($currentDesigner->id);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }

    /**
     * Get unread message count for current user (API endpoint)
     */
    public function getUnreadCount($locale)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json(['count' => 0]);
            }

            $totalUnread = cache()->remember(
                "unread_messages_{$currentDesigner->id}",
                30,
                fn() => Conversation::where(function($q) use ($currentDesigner) {
                    $q->where('designer_1_id', $currentDesigner->id)
                      ->orWhere('designer_2_id', $currentDesigner->id);
                })
                ->selectRaw("
                    SUM(CASE
                        WHEN designer_1_id = ? THEN designer_1_unread_count
                        ELSE designer_2_unread_count
                    END) as total_unread
                ", [$currentDesigner->id])
                ->value('total_unread') ?? 0
            );

            return response()->json(['count' => $totalUnread]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Find conversation between two designers
     */
    private function findConversation($designerId1, $designerId2)
    {
        return Conversation::where(function ($query) use ($designerId1, $designerId2) {
            $query->where('designer_1_id', min($designerId1, $designerId2))
                  ->where('designer_2_id', max($designerId1, $designerId2));
        })->first();
    }

    /**
     * Check if a designer is a participant in a conversation
     */
    private function isParticipant(Conversation $conversation, $designerId)
    {
        return $conversation->designer_1_id == $designerId || $conversation->designer_2_id == $designerId;
    }

    /**
     * Get the other participant's ID
     */
    private function getReceiverId(Conversation $conversation, $senderId)
    {
        return $conversation->designer_1_id == $senderId
            ? $conversation->designer_2_id
            : $conversation->designer_1_id;
    }

    /**
     * Create a message in a conversation and update metadata
     */
    private function createMessageInConversation(Conversation $conversation, $senderId, $receiverId, $messageText)
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => strip_tags($messageText),
        ]);

        $conversation->last_message_id = $message->id;
        $conversation->last_message_at = now();

        if ($conversation->designer_1_id == $receiverId) {
            $conversation->designer_1_unread_count++;
        } else {
            $conversation->designer_2_unread_count++;
        }
        $conversation->save();

        // Throttled notification - max 1 per 5 min per conversation
        $recentNotification = Notification::where('designer_id', $receiverId)
            ->where('type', 'new_message')
            ->where('data->conversation_id', $conversation->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$recentNotification) {
            $sender = Designer::find($senderId);
            Notification::create([
                'designer_id' => $receiverId,
                'type' => 'new_message',
                'title' => 'New Message',
                'message' => ($sender->name ?? 'Someone') . ' sent you a message.',
                'data' => json_encode(['conversation_id' => $conversation->id]),
                'read' => false
            ]);
        }

        return $message;
    }
}
