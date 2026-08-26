@extends('emails.layouts.system')
@php($badgeColor = '#d97706')

@section('badge')
    {{ __('emails.password_changed.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.password_changed.heading', [], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.password_changed.greeting', ['name' => $userName], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.password_changed.body', ['app' => config('app.name')], $locale) }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;">
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">{{ __('emails.password_changed.time', [], $locale) }}</td>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 700; text-align: right; color: #111827; font-size: 13px;">{{ $time }}</td>
        </tr>
        <tr>
            <td style="padding: 14px 20px; color: #6b7280; font-size: 13px;">{{ __('emails.password_changed.ip_address', [], $locale) }}</td>
            <td style="padding: 14px 20px; font-weight: 700; text-align: right; color: #111827; font-size: 13px;">{{ $ipAddress }}</td>
        </tr>
    </table>

    <p class="text-body" style="color: #374151;">{{ __('emails.password_changed.safe', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;"><strong>{{ __('emails.password_changed.warning_lead', [], $locale) }}</strong>{{ __('emails.password_changed.warning_tail', [], $locale) }}</p>

    @component('emails.components.button', ['url' => $settingsUrl])
        {{ __('emails.password_changed.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.password_changed.thanks', [], $locale) }}<br>{{ __('emails.password_changed.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
