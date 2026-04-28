<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MessageRequest;
use App\Models\Designer;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * Manages message requests between designers before a conversation is established.
 * A request must be accepted before real-time chat becomes available; acceptance creates a Conversation record.
 */
class MessageRequestController extends Controller
{
    /**
     * Show the inbox of received and sent message requests for the current user.
     *
     * @param  string  $locale
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index($locale)
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
     * Send a new message request to another designer (enforces no duplicates and no existing conversation).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $designerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request, $locale, $designerId)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json([
                    'success' => false,
                    'message' => __('Please login to send message requests')
                ], 401);
            }

            if ($currentDesigner->id == $designerId) {
                return response()->json([
                    'success' => false,
                    'message' => __('You cannot message yourself')
                ], 400);
            }

            $recipient = Designer::find($designerId);

            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Designer not found'
                ], 404);
            }

            if (!$recipient->allow_messages) {
                return response()->json([
                    'success' => false,
                    'message' => __('This designer is not accepting messages')
                ], 403);
            }

            // Check for existing conversation
            $conversation = Conversation::where(function ($query) use ($currentDesigner, $designerId) {
                $query->where('designer_1_id', min($currentDesigner->id, $designerId))
                      ->where('designer_2_id', max($currentDesigner->id, $designerId));
            })->first();

            if ($conversation) {
                return response()->json([
                    'success' => false,
                    'message' => __('You already have a conversation with this designer')
                ], 400);
            }

            // Check for existing pending request
            $existingRequest = MessageRequest::where('from_designer_id', $currentDesigner->id)
                ->where('to_designer_id', $designerId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => __('You already have a pending message request with this designer')
                ], 400);
            }

            // Validate, length-cap, and strip HTML from the user-supplied
            // message before persisting. Without this, a crafted body could
            // store XSS or oversized text shown later in notifications/UI.
            // (bugs.md B-6)
            $request->validate([
                'message' => 'nullable|string|max:2000',
            ]);
            $customMessage = trim(strip_tags(
                $request->input('message') ?: "Hi {$recipient->name}, I'd like to connect with you!"
            ));

            MessageRequest::create([
                'from_designer_id' => $currentDesigner->id,
                'to_designer_id' => $designerId,
                'status' => 'pending',
                'message' => $customMessage
            ]);

            Notification::create([
                'designer_id' => $designerId,
                'type' => 'message_request',
                'title' => 'New Message Request',
                'message' => $currentDesigner->name . ' sent you a message request.',
                'read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Message request sent successfully! They will be notified.')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending message request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while sending the message request. Please try again.')
            ], 500);
        }
    }

    /**
     * Accept a message request, creating a Conversation and notifying the requester.
     *
     * @param  string  $locale
     * @param  int     $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept($locale, $requestId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $messageRequest = MessageRequest::findOrFail($requestId);

        if ($messageRequest->to_designer_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation = $messageRequest->accept();

        Notification::create([
            'designer_id' => $messageRequest->from_designer_id,
            'type' => 'message_request_accepted',
            'title' => 'Message Request Accepted',
            'message' => $currentDesigner->name . ' accepted your message request. You can now chat!',
            'read' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Message request accepted'),
            'redirect' => route('messages.chat', [
                'locale' => $locale,
                'designerId' => $messageRequest->from_designer_id
            ])
        ]);
    }

    /**
     * Decline and mark a message request as rejected.
     *
     * @param  string  $locale
     * @param  int     $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function decline($locale, $requestId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $messageRequest = MessageRequest::findOrFail($requestId);

        if ($messageRequest->to_designer_id != $currentDesigner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $messageRequest->decline();

        return response()->json([
            'success' => true,
            'message' => __('Message request declined')
        ]);
    }

    /**
     * Check whether the current user has a pending outgoing request to a given designer.
     *
     * @param  string  $locale
     * @param  int     $designerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPending($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'has_pending_request' => false,
                'has_conversation' => false,
            ]);
        }

        // Check for existing conversation first
        $conversation = Conversation::where(function ($query) use ($currentDesigner, $designerId) {
            $query->where('designer_1_id', min($currentDesigner->id, (int) $designerId))
                  ->where('designer_2_id', max($currentDesigner->id, (int) $designerId));
        })->first();

        if ($conversation) {
            return response()->json([
                'success' => true,
                'has_pending_request' => false,
                'has_conversation' => true,
                'conversation_url' => url($locale . '/messages/chat/' . $designerId),
            ]);
        }

        $hasPendingRequest = MessageRequest::where('from_designer_id', $currentDesigner->id)
            ->where('to_designer_id', $designerId)
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'success' => true,
            'has_pending_request' => $hasPendingRequest,
            'has_conversation' => false,
        ]);
    }

    /**
     * Return the count of pending message requests received by the current user.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingCount($locale)
    {
        try {
            $currentDesigner = auth('designer')->user();

            if (!$currentDesigner) {
                return response()->json(['count' => 0]);
            }

            $pendingCount = MessageRequest::where('to_designer_id', $currentDesigner->id)
                ->where('status', 'pending')
                ->count();

            return response()->json(['count' => $pendingCount]);
        } catch (\Exception $e) {
            \Log::error('Error getting pending requests count: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }
}
