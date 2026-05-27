@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Welcome to {{ config('app.name') }}!</h2>

    <p>Hello {{ $userName }},</p>

    <p>Thank you for joining Filkx! To get started with your 24/7 automated video streaming service, please verify your
        email address by clicking the button below:</p>

    @component('emails.components.button', ['url' => $verificationUrl])
        Verify Email Address
    @endcomponent

    <p>If you did not create an account, no further action is required.</p>

    @include('emails.components.divider')

    <p>Need help? Simply reply to this email or visit our <a href="{{ config('app.url') }}/support"
                                                             style="color: #3182ce; text-decoration: none;">Support
            Center</a>.</p>

    <p>Happy streaming,<br>{{ config('app.name') }} Team</p>
@endsection
