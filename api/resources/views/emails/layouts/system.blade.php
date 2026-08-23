<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; padding: 32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
                <tr>
                    <td style="padding: 32px 40px 0 40px;">
                        <a href="{{ config('app.frontend_url', config('app.url')) }}" style="text-decoration: none; color: #2d3748; font-size: 20px; font-weight: bold;">
                            {{ config('app.name') }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 24px 40px 40px 40px; color: #4a5568; font-size: 15px; line-height: 1.6;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding: 24px 40px; background-color: #f7fafc; color: #a0aec0; font-size: 12px; text-align: center;">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
