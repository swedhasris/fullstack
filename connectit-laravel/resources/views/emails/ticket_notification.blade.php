<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Ticket Notification' }}</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: #151B26; padding: 24px 32px; }
        .header-logo { color: #81B532; font-size: 20px; font-weight: 900; letter-spacing: -0.5px; }
        .header-subtitle { color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .body { padding: 32px; }
        .ticket-badge { display: inline-block; background: #81B532; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 16px; }
        h2 { color: #1a1a1a; font-size: 18px; font-weight: 700; margin: 0 0 16px; }
        p { color: #4b5563; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
        .message-box { background: #f9fafb; border-left: 4px solid #81B532; padding: 16px; border-radius: 0 8px 8px 0; margin: 20px 0; }
        .btn { display: inline-block; background: #81B532; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 8px; }
        .footer { background: #f9fafb; padding: 20px 32px; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-logo">ConnectIT ITSM</div>
            <div class="header-subtitle">IT Service Management</div>
        </div>
        <div class="body">
            @if(isset($ticket))
            <div class="ticket-badge">{{ $ticket->ticket_number }}</div>
            <h2>{{ $ticket->title }}</h2>
            @endif

            <div class="message-box">
                <p style="margin:0; color: #374151;">{{ $message_body ?? 'You have a new notification regarding your ticket.' }}</p>
            </div>

            @if(isset($ticket))
            <table style="width:100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="padding: 8px 0; color: #9ca3af; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 40%;">Status</td>
                    <td style="padding: 8px 0; color: #374151; font-size: 13px;">{{ is_object($ticket->status) ? $ticket->status->value : $ticket->status }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #9ca3af; font-size: 12px; font-weight: 700; text-transform: uppercase;">Priority</td>
                    <td style="padding: 8px 0; color: #374151; font-size: 13px;">{{ is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority }}</td>
                </tr>
                @if($ticket->assigned_to_name)
                <tr>
                    <td style="padding: 8px 0; color: #9ca3af; font-size: 12px; font-weight: 700; text-transform: uppercase;">Assigned To</td>
                    <td style="padding: 8px 0; color: #374151; font-size: 13px;">{{ $ticket->assigned_to_name }}</td>
                </tr>
                @endif
            </table>

            <a href="{{ config('app.url') }}/tickets/{{ $ticket->id }}" class="btn">View Ticket →</a>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated notification from ConnectIT ITSM. Please do not reply to this email.</p>
            <p style="margin-top: 8px;">© {{ date('Y') }} ConnectIT ITSM. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
