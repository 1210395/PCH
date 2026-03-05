<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRequest;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{
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

        // Can't message yourself
        if ($currentDesigner->id == $designerId) {
            return redirect()->back()->with('error', 'You cannot message yourself');
        }

        $recipient = Designer::findOrFail($designerId);

        // Check if recipient allows messages
        if (!$recipient->allow_messages) {
            return redirect()->back()->with('error', 'This designer is not accepting messages');
        }

        // Check if there's an existing conversation
        $conversation = Conversation::where(function ($query) use ($currentDesigner, $designerId) {
            $query->where('designer_1_id', min($currentDesigner->id, $designerId))
                  ->where('designer_2_id', max($currentDesigner->id, $designerId));
        })->first();

        // If conversation exists, redirect to chat
        if ($conversation) {
            return redirect()->route('messages.chat', [
                'locale' => $locale,
                'designerId' => $designerId
            ]);
        }

        // Check for pending message request
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

        // Check if recipient allows messages
        if (!$recipient->allow_messages) {
            return response()->json([
                'success' => false,
                'message' => 'This designer is not accepting messages'
            ], 403);
        }

        // Check for existing conversation
        $conversation = Conversation::where(function ($query) use ($currentDesigner, $designerId) {
            $query->where('designer_1_id', min($currentDesigner->id, $designerId))
                  ->where('designer_2_id', max($currentDesigner->id, $designerId));
        })->first();

        if ($conversation) {
            // Send message directly
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $currentDesigner->id,
                'receiver_id' => $designerId,
                'message' => strip_tags($validated['message']),
            ]);

            // Update conversation
            $conversation->last_message_id = $message->id;
            $conversation->last_message_at = now();

            // Increment unread count for receiver
            if ($conversation->designer_1_id == $designerId) {
                $conversation->designer_1_unread_count++;
            } else {
                $conversation->designer_2_unread_count++;
            }
            $conversation->save();

            // Create notification for receiver (throttled - max 1 per 5 min per conversation)
            $recentNotification = \App\Models\Notification::where('designer_id', $designerId)
                ->where('type', 'new_message')
                ->where('data->conversation_id', $conversation->id)
                ->where('created_at', '>', now()->subMinutes(5))
                ->first();

            if (!$recentNotification) {
                \App\Models\Notification::create([
                    'designer_id' => $designerId,
                    'type' => 'new_message',
                    'title' => 'New Message',
                    'message' => $currentDesigner->name . ' sent you a message.',
                    'data' => json_encode(['conversation_id' => $conversation->id]),
                    'read' => false
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'redirect' => route('messages.chat', ['locale' => $locale, 'designerId' => $designerId])
            ]);
        }

        // No conversation exists - create message request
        $messageRequest = MessageRequest::create([
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

        // Get or create conversation
        $conversation = Conversation::findOrCreateBetween($currentDesigner->id, $designerId);

        // Mark messages as read
        $conversation->markAsRead($currentDesigner->id);

        // Get all messages in this conversation
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

        // Verify current designer is part of this conversation
        if ($conversation->designer_1_id != $currentDesigner->id &&
            $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Determine receiver
        $receiverId = $conversation->designer_1_id == $currentDesigner->id
            ? $conversation->designer_2_id
            : $conversation->designer_1_id;

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $currentDesigner->id,
            'receiver_id' => $receiverId,
            'message' => strip_tags($validated['message']),
        ]);

        // Update conversation
        $conversation->last_message_id = $message->id;
        $conversation->last_message_at = now();

        // Increment unread count for receiver
        if ($conversation->designer_1_id == $receiverId) {
            $conversation->designer_1_unread_count++;
        } else {
            $conversation->designer_2_unread_count++;
        }
        $conversation->save();

        // Create notification for receiver (throttled - max 1 per 5 min per conversation)
        $recentNotification = \App\Models\Notification::where('designer_id', $receiverId)
            ->where('type', 'new_message')
            ->where('data->conversation_id', $conversation->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$recentNotification) {
            \App\Models\Notification::create([
                'designer_id' => $receiverId,
                'type' => 'new_message',
                'title' => 'New Message',
                'message' => $currentDesigner->name . ' sent you a message.',
                'data' => json_encode(['conversation_id' => $conversation->id]),
                'read' => false
            ]);
        }

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

        // Verify access
        if ($conversation->designer_1_id != $currentDesigner->id &&
            $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $lastMessageId = $request->input('last_message_id', 0);

        $messages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark new messages as read
        $conversation->markAsRead($currentDesigner->id);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Show message requests
     */
    public function requests($locale)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return redirect()->route('login', ['locale' => $locale]);
        }

        $receivedRequests = MessageRequest::with('fromDesigner')
            ->where('to_designer_id', $currentDesigner->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $sentRequests = MessageRequest::with('toDesigner')
            ->where('from_designer_id', $currentDesigner->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('messages.requests', [
            'receivedRequests' => $receivedRequests,
            'sentRequests' => $sentRequests
        ]);
    }

    /**
     * Accept a message request
     */
    public function acceptRequest($locale, $requestId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $messageRequest = MessageRequest::findOrFail($requestId);

        // Verify this request is for current designer
        if ($messageRequest->to_designer_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation = $messageRequest->accept();

        // Create notification for the sender that their request was accepted
        \App\Models\Notification::create([
            'designer_id' => $messageRequest->from_designer_id,
            'type' => 'message_request_accepted',
            'title' => 'Message Request Accepted',
            'message' => $currentDesigner->name . ' accepted your message request. You can now chat!',
            'read' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message request accepted',
            'redirect' => route('messages.chat', [
                'locale' => $locale,
                'designerId' => $messageRequest->from_designer_id
            ])
        ]);
    }

    /**
     * Decline a message request
     */
    public function declineRequest($locale, $requestId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $messageRequest = MessageRequest::findOrFail($requestId);

        // Verify this request is for current designer
        if ($messageRequest->to_designer_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $messageRequest->decline();

        return response()->json([
            'success' => true,
            'message' => 'Message request declined'
        ]);
    }

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
     * Get unread message count for current user (API endpoint)
     */
    public function getUnreadCount($locale)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json(['count' => 0]);
            }

            // Calculate total unread messages with a single aggregation query
            $totalUnread = Conversation::where(function($q) use ($currentDesigner) {
                $q->where('designer_1_id', $currentDesigner->id)
                  ->orWhere('designer_2_id', $currentDesigner->id);
            })
            ->selectRaw("
                SUM(CASE
                    WHEN designer_1_id = ? THEN designer_1_unread_count
                    ELSE designer_2_unread_count
                END) as total_unread
            ", [$currentDesigner->id])
            ->value('total_unread') ?? 0;

            return response()->json(['count' => $totalUnread]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Get pending message requests count for current user (API endpoint)
     */
    public function getPendingRequestsCount($locale)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json(['count' => 0]);
            }

            // Count pending message requests sent TO the current user
            $pendingCount = MessageRequest::where('to_designer_id', $currentDesigner->id)
                ->where('status', 'pending')
                ->count();

            return response()->json(['count' => $pendingCount]);
        } catch (\Exception $e) {
            \Log::error('Error getting pending requests count: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Send a message request to another designer
     */
    public function sendRequest(Request $request, $locale, $designerId)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to send message requests'
                ], 401);
            }

            // Can't message yourself
            if ($currentDesigner->id == $designerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot message yourself'
                ], 400);
            }

            $recipient = Designer::find($designerId);

            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Designer not found'
                ], 404);
            }

            // Check if recipient allows messages
            if (!$recipient->allow_messages) {
                return response()->json([
                    'success' => false,
                    'message' => 'This designer is not accepting messages'
                ], 403);
            }

            // Check if there's already an existing conversation
            $conversation = Conversation::where(function ($query) use ($currentDesigner, $designerId) {
                $query->where('designer_1_id', min($currentDesigner->id, $designerId))
                      ->where('designer_2_id', max($currentDesigner->id, $designerId));
            })->first();

            if ($conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a conversation with this designer'
                ], 400);
            }

            // Check if there's already a pending request
            $existingRequest = MessageRequest::where('from_designer_id', $currentDesigner->id)
                ->where('to_designer_id', $designerId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending message request with this designer'
                ], 400);
            }

            // Get custom message from request or use default
            $customMessage = $request->input('message', "Hi {$recipient->name}, I'd like to connect with you!");

            // Create message request
            MessageRequest::create([
                'from_designer_id' => $currentDesigner->id,
                'to_designer_id' => $designerId,
                'status' => 'pending',
                'message' => $customMessage
            ]);

            // Create notification for the recipient
            \App\Models\Notification::create([
                'designer_id' => $designerId,
                'type' => 'message_request',
                'title' => 'New Message Request',
                'message' => $currentDesigner->name . ' sent you a message request.',
                'read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message request sent successfully! They will be notified.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending message request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the message request. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if a pending message request exists
     */
    public function checkPendingRequest($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'has_pending_request' => false
            ]);
        }

        // Check if there's a pending request from current user to this designer
        $hasPendingRequest = MessageRequest::where('from_designer_id', $currentDesigner->id)
            ->where('to_designer_id', $designerId)
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'success' => true,
            'has_pending_request' => $hasPendingRequest
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

        // Verify access
        if ($conversation->designer_1_id != $currentDesigner->id &&
            $conversation->designer_2_id != $currentDesigner->id) {
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
     * Send a message in a conversation (for chat panel)
     */
    public function sendMessageInConversation($locale, $conversationId, Request $request)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        // Verify current designer is part of this conversation
        if ($conversation->designer_1_id != $currentDesigner->id &&
            $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Determine receiver
        $receiverId = $conversation->designer_1_id == $currentDesigner->id
            ? $conversation->designer_2_id
            : $conversation->designer_1_id;

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $currentDesigner->id,
            'receiver_id' => $receiverId,
            'message' => strip_tags($validated['message']),
        ]);

        // Update conversation
        $conversation->last_message_id = $message->id;
        $conversation->last_message_at = now();

        // Increment unread count for receiver
        if ($conversation->designer_1_id == $receiverId) {
            $conversation->designer_1_unread_count++;
        } else {
            $conversation->designer_2_unread_count++;
        }
        $conversation->save();

        // Create notification for receiver (throttled - max 1 per 5 min per conversation)
        $recentNotification = \App\Models\Notification::where('designer_id', $receiverId)
            ->where('type', 'new_message')
            ->where('data->conversation_id', $conversation->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$recentNotification) {
            \App\Models\Notification::create([
                'designer_id' => $receiverId,
                'type' => 'new_message',
                'title' => 'New Message',
                'message' => $currentDesigner->name . ' sent you a message.',
                'data' => json_encode(['conversation_id' => $conversation->id]),
                'read' => false
            ]);
        }

        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => $message
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

        // Verify access
        if ($conversation->designer_1_id != $currentDesigner->id &&
            $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation->markAsRead($currentDesigner->id);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }
}
