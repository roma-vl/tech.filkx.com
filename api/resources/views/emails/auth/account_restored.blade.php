@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Welcome back, {{ $userName }}!</h2>

    <p>Your account at <strong>{{ config('app.name') }}</strong> has been successfully restored.</p>

    <p>All your data, streams, and settings have been preserved exactly as you left them.</p>

    <p>You can now log in to your account and resume your activities:</p>

    @component('emails.components.button', ['url' => $loginUrl])
        Login to Your Account
    @endcomponent

    @include('emails.components.divider')

    <p>If you have any questions or noticed anything unusual, please contact our support team.</p>

    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
