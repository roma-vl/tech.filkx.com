@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Reset Your Password</h2>

    <p>Hello,</p>

    <p>We received a password reset request for your <strong>{{ config('app.name') }}</strong> account.</p>

    <p>If you made this request, please click the button below to set a new password:</p>

    @component('emails.components.button', ['url' => $resetUrl])
        Reset Password
    @endcomponent

    <p>This password reset link will expire in <strong>{{ $expire }} minutes</strong>.</p>

    @include('emails.components.divider')

    <p>If you did not request a password reset, no further action is required. Your account remains secure.</p>

    <p>Thanks,<br>{{ config('app.name') }} Team</p>
@endsection
