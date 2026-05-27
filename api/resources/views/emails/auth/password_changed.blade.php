@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Password Changed</h2>

    <p>Hello {{ $userName }},</p>

    <p>The password for your <strong>{{ config('app.name') }}</strong> account was recently changed.</p>

    <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Time</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $time }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">IP Address</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $ipAddress }}</td>
        </tr>
    </table>

    <p>If you did this, you can safely ignore this email.</p>

    <p><strong>If you did not change your password</strong>, please secure your account immediately by resetting your
        password or contacting support.</p>

    @component('emails.components.button', ['url' => $settingsUrl])
        Review Security Settings
    @endcomponent

    @include('emails.components.divider')

    <p>Thanks,<br>{{ config('app.name') }} Team</p>
@endsection
