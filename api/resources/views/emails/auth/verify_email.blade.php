@extends('emails.layouts.system')
@php($badgeColor = '#00a046')

@section('badge')
    {{ __('emails.verify_email.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.verify_email.heading', ['app' => config('app.name')], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.verify_email.greeting', ['name' => $userName], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.verify_email.body', ['app' => config('app.name')], $locale) }}</p>

    @component('emails.components.button', ['url' => $verificationUrl])
        {{ __('emails.verify_email.button', [], $locale) }}
    @endcomponent

    <p class="text-muted" style="color: #6b7280; font-size: 13px;">{{ __('emails.verify_email.note', [], $locale) }}</p>

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.verify_email.help', [], $locale) }}
        <a href="{{ config('app.frontend_url', config('app.url')) }}/support" style="color: #00a046; text-decoration: none; font-weight: 600;">{{ __('emails.verify_email.support_center', [], $locale) }}</a>.
    </p>

    <p class="text-body" style="color: #374151;">{{ __('emails.verify_email.thanks', [], $locale) }}<br>{{ __('emails.verify_email.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
