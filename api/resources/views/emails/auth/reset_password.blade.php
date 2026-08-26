@extends('emails.layouts.system')
@php($badgeColor = '#00a046')

@section('badge')
    {{ __('emails.reset_password.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.reset_password.heading', [], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.reset_password.greeting', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.reset_password.body', ['app' => config('app.name')], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.reset_password.instruction', [], $locale) }}</p>

    @component('emails.components.button', ['url' => $resetUrl])
        {{ __('emails.reset_password.button', [], $locale) }}
    @endcomponent

    <p class="text-muted" style="color: #6b7280; font-size: 13px;">{{ __('emails.reset_password.expiry', ['minutes' => $expire], $locale) }}</p>

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.reset_password.ignore', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.reset_password.thanks', [], $locale) }}<br>{{ __('emails.reset_password.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
