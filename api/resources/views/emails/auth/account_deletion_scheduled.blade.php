@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Hello, {{ $userName }}!</h2>

    <p>Your account at <strong>{{ config('app.name') }}</strong> has been scheduled for permanent deletion.</p>

    <p>All associated data, including your streams, playlists, and subscription history, will be deleted in <strong>3
            days</strong>.</p>

    <p>If you did not request this, or have changed your mind, you can restore your account immediately by clicking the
        button below:</p>

    @component('emails.components.button', ['url' => $restoreUrl])
        Restore My Account
    @endcomponent

    <p>This restoration link is valid for 3 days.</p>

    @include('emails.components.divider')

    <p>If you have any questions or need assistance, please reply to this email or contact our support team.</p>

    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
