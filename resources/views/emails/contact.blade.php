<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px;
        }
        .sender-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .sender-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .sender-info .label {
            color: #666;
        }
        .sender-info .value {
            color: #333;
            font-weight: 500;
        }
        .message-content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .reply-note {
            background-color: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #004085;
        }
        .reply-note strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>New Message</h1>
        </div>

        <div class="email-body">
            <p>Hello {{ $recipientName }},</p>

            <p>You have received a new message from a designer on our platform:</p>

            <div class="sender-info">
                <p><span class="label">From:</span> <span class="value">{{ $senderName }}</span></p>
                <p><span class="label">Email:</span> <span class="value">{{ $senderEmail }}</span></p>
                <p><span class="label">Subject:</span> <span class="value">{{ $subject }}</span></p>
            </div>

            <h3 style="margin-bottom: 10px; color: #333;">Message:</h3>
            <div class="message-content">{{ $messageBody }}</div>

            <div class="reply-note">
                <strong>How to reply:</strong>
                You can reply directly to this email - your response will be sent to {{ $senderName }} at {{ $senderEmail }}.
            </div>
        </div>

        <div class="email-footer">
            <p>This email was sent through our designer platform.</p>
            <p>If you did not expect this message, please ignore it.</p>
        </div>
    </div>
</body>
</html>
