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
        }
        .container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Queue Test Successful!</h1>
        </div>
        <div class="content">
            <p><strong>Congratulations!</strong> Your queued email system is working correctly.</p>

            <div class="badge">✓ Queue Processing Active</div>

            <div class="info-box">
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li><strong>Message:</strong> {{ $testMessage }}</li>
                    <li><strong>Queued At:</strong> {{ $queuedAt }}</li>
                    <li><strong>Sent At:</strong> {{ now()->toDateTimeString() }}</li>
                    <li><strong>Queue Connection:</strong> <code>{{ config('queue.default') }}</code></li>
                    <li><strong>Mail Driver:</strong> <code>{{ config('mail.default') }}</code></li>
                </ul>
            </div>

            <h3>What this means:</h3>
            <p>This email was successfully:</p>
            <ol>
                <li>Queued to your database queue</li>
                <li>Picked up by a queue worker</li>
                <li>Processed and sent via your mail driver</li>
                <li>Delivered to your inbox</li>
            </ol>

            <p>Your production email queue is functioning correctly! 🎉</p>
        </div>

        <div class="footer">
            <p>This is an automated test email from {{ config('app.name') }}</p>
            <p>Generated at {{ now()->toDateTimeString() }}</p>
        </div>
    </div>
</body>
</html>
