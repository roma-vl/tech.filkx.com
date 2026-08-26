@extends('emails.layouts.system')
@php($badgeColor = '#00a046')

@section('badge')
    {{ __('emails.account_restored.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.account_restored.heading', ['name' => $userName], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_restored.body_1', ['app' => config('app.name')], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_restored.body_2', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_restored.body_3', [], $locale) }}</p>

    @component('emails.components.button', ['url' => $loginUrl])
        {{ __('emails.account_restored.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.account_restored.help', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.account_restored.regards', [], $locale) }}<br>{{ __('emails.account_restored.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
