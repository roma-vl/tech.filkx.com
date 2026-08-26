<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ config('app.name') }}</title>
    <!--[if mso]>
    <style>
        table {border-collapse: collapse;}
        .fallback-font {font-family: Arial, sans-serif;}
    </style>
    <![endif]-->
    <style>
        /* Progressive enhancement only — every element below also carries an inline style
           so the email still looks correct in clients that strip <style> blocks entirely. */
        @media (prefers-color-scheme: dark) {
            .email-bg { background-color: #0b0f14 !important; }
            .card-bg { background-color: #161b22 !important; }
            .text-body { color: #cbd5e1 !important; }
            .text-heading { color: #f1f5f9 !important; }
            .text-muted { color: #8b96a5 !important; }
            .divider-line { border-top-color: #2a323d !important; }
            .footer-bg { background-color: #10151c !important; border-top-color: #232b35 !important; }
        }
    </style>
</head>
<body class="email-bg" style="margin: 0; padding: 0; background-color: #eef1f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-bg" style="background-color: #eef1f5; padding: 32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px;">
                <!-- Brand header -->
                <tr>
                    <td style="background-color: #1c1b1b; border-radius: 14px 14px 0 0; padding: 22px 32px; border-bottom: 3px solid #00a046;">
                        <table role="presentation" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="vertical-align: middle; padding-right: 10px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="32" height="32" style="background-color: #00a046; border-radius: 8px;">
                                        <tr>
                                            <td align="center" valign="middle" width="32" height="32" style="color: #ffffff; font-size: 16px; font-weight: 800; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">F</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="vertical-align: middle;">
                                    <a href="{{ config('app.frontend_url', config('app.url')) }}" style="text-decoration: none; color: #ffffff; font-size: 19px; font-weight: 800; letter-spacing: -0.3px;">
                                        {{ config('app.name') }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @hasSection('badge')
                    <tr>
                        <td style="background-color: {{ $badgeColor ?? '#00a046' }}; padding: 12px 32px;">
                            <span style="font-size: 12px; font-weight: 700; color: #ffffff; letter-spacing: 1px; text-transform: uppercase;">
                                @yield('badge')
                            </span>
                        </td>
                    </tr>
                @endif

                <!-- Content card -->
                <tr>
                    <td class="card-bg" style="background-color: #ffffff; padding: 32px; color: #374151; font-size: 15px; line-height: 1.65;">
                        @yield('content')
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer-bg" style="background-color: #f7fafc; border-radius: 0 0 14px 14px; padding: 24px 32px; text-align: center; border-top: 1px solid #e5e7eb;">
                        <p class="text-muted" style="margin: 0 0 8px; color: #9aa4b2; font-size: 12px;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('emails.layout.rights', [], $locale ?? config('app.fallback_locale')) }}
                        </p>
                        <p style="margin: 0; font-size: 12px;">
                            <a href="{{ config('app.frontend_url', config('app.url')) }}/terms" style="color: #00a046; text-decoration: none; font-weight: 600;">{{ __('emails.layout.terms', [], $locale ?? config('app.fallback_locale')) }}</a>
                            <span class="text-muted" style="color: #c3cad3; padding: 0 8px;">&middot;</span>
                            <a href="{{ config('app.frontend_url', config('app.url')) }}/privacy" style="color: #00a046; text-decoration: none; font-weight: 600;">{{ __('emails.layout.privacy', [], $locale ?? config('app.fallback_locale')) }}</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
