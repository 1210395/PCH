<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Handles direct email composition and delivery between authenticated designers.
 * Only allows contact with designers who have enabled the "show email" privacy setting.
 */
class EmailController extends Controller
{
    /**
     * Show the email compose form
     */
    public function compose($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('error', 'Please login to send emails');
        }

        // Can't email yourself
        if ($currentDesigner->id == $designerId) {
            return redirect()->back()->with('error', 'You cannot email yourself');
        }

        $recipient = Designer::findOrFail($designerId);

        // Check if recipient has email and shows it
        if (!$recipient->email || !$recipient->show_email) {
            return redirect()->back()->with('error', 'This designer is not accepting emails');
        }

        return view('email.compose', [
            'recipient' => $recipient,
            'sender' => $currentDesigner
        ]);
    }

    /**
     * Send the email
     */
    public function send(Request $request, $locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $recipient = Designer::find($designerId);

        if (!$recipient) {
            return response()->json(['success' => false, 'message' => 'Designer not found'], 404);
        }

        // Check if recipient has email and shows it
        if (!$recipient->email || !$recipient->show_email) {
            return response()->json([
                'success' => false,
                'message' => 'This designer is not accepting emails'
            ], 403);
        }

        try {
            // Send email using Laravel Mail
            Mail::send('emails.contact', [
                'senderName' => $currentDesigner->name,
                'senderEmail' => $currentDesigner->email,
                'subject' => strip_tags($request->subject),
                'messageBody' => strip_tags($request->message),
                'recipientName' => $recipient->name,
            ], function ($mail) use ($recipient, $currentDesigner, $request) {
                $mail->to($recipient->email, $recipient->name)
                     ->replyTo($currentDesigner->email, $currentDesigner->name)
                     ->subject(strip_tags($request->subject));
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
