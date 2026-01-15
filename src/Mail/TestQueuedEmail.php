<?php

namespace Ingenius\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestQueuedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $testMessage;
    public string $queuedAt;

    /**
     * Create a new message instance.
     */
    public function __construct(string $testMessage = 'This is a test queued email')
    {
        $this->testMessage = $testMessage;
        $this->queuedAt = now()->toDateTimeString();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Queued Email - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $html = $this->generateHtml();

        return new Content(
            htmlString: $html,
        );
    }

    /**
     * Generate the HTML content for the email
     */
    protected function generateHtml(): string
    {
        $appName = config('app.name');
        $queueConnection = config('queue.default');
        $mailDriver = config('mail.default');
        $sentAt = now()->toDateTimeString();

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Queued Email</title>
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
        .container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-box li {
            margin: 8px 0;
        }
        code {
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #dc2626;
        }
        .success-list {
            background: #f0fdf4;
            border: 1px solid #86efac;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success-list ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .success-list li {
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .emoji {
            font-size: 48px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">✅</div>
            <h1>Queue Test Successful!</h1>
        </div>

        <div class="content">
            <p><strong>Congratulations!</strong> Your queued email system is working correctly.</p>

            <div class="badge">✓ Queue Processing Active</div>

            <div class="info-box">
                <p><strong>📋 Test Details:</strong></p>
                <ul>
                    <li><strong>Test Message:</strong> {$this->testMessage}</li>
                    <li><strong>Queued At:</strong> {$this->queuedAt}</li>
                    <li><strong>Sent At:</strong> {$sentAt}</li>
                    <li><strong>Queue Connection:</strong> <code>{$queueConnection}</code></li>
                    <li><strong>Mail Driver:</strong> <code>{$mailDriver}</code></li>
                </ul>
            </div>

            <div class="success-list">
                <p><strong>✨ What this means:</strong></p>
                <p>This email was successfully:</p>
                <ol>
                    <li>Queued to your <code>{$queueConnection}</code> queue</li>
                    <li>Picked up by a queue worker process</li>
                    <li>Processed and sent via <code>{$mailDriver}</code> mail driver</li>
                    <li>Delivered to your inbox</li>
                </ol>
            </div>

            <p style="text-align: center; font-size: 18px; margin: 30px 0;">
                <strong>Your production email queue is functioning correctly! 🎉</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated test email from <strong>{$appName}</strong></p>
            <p>Generated at {$sentAt}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
