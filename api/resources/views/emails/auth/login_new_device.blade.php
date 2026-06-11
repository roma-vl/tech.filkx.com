@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">New Login Detected</h2>

    <p>Hello {{ $userName }},</p>

    <p>We noticed a login to your <strong>{{ config('app.name') }}</strong> account from a new device or location.</p>

    <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Device</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $deviceName }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Location</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $location }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Time</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $time }}</td>
        </tr>
    </table>

    <p>If this was you, you can safely ignore this email.</p>

    <p><strong>If you don't recognize this activity</strong>, please secure your account immediately:</p>

    @component('emails.components.button', ['url' => $settingsUrl])
        Review Active Sessions
    @endcomponent

    @include('emails.components.divider')

    <p>Thanks,<br>{{ config('app.name') }} Team</p>
@endsection
