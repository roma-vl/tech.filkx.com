@extends('emails.layouts.system')
@php($badgeColor = '#dc2626')

@section('badge')
    {{ __('emails.account_deletion_scheduled.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.account_deletion_scheduled.heading', ['name' => $userName], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_deletion_scheduled.body_1', ['app' => config('app.name')], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_deletion_scheduled.body_2', ['days' => 3], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_deletion_scheduled.body_3', [], $locale) }}</p>

    @component('emails.components.button', ['url' => $restoreUrl])
        {{ __('emails.account_deletion_scheduled.button', [], $locale) }}
    @endcomponent

    <p class="text-muted" style="color: #6b7280; font-size: 13px;">{{ __('emails.account_deletion_scheduled.note', ['days' => 3], $locale) }}</p>

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.account_deletion_scheduled.help', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_deletion_scheduled.regards', [], $locale) }}<br>{{ __('emails.account_deletion_scheduled.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
