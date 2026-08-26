@extends('emails.layouts.system')
@php($badgeColor = '#d97706')

@section('badge')
    {{ __('emails.login_new_device.badge', [], $locale) }}
@endsection

@section('content')
    <h2 class="text-heading" style="color: #111827; margin: 0 0 16px; font-size: 21px;">{{ __('emails.login_new_device.heading', [], $locale) }}</h2>

    <p class="text-body" style="color: #374151;">{{ __('emails.login_new_device.greeting', ['name' => $userName], $locale) }}</p>

    <p class="text-body" style="color: #374151;">{{ __('emails.login_new_device.body', ['app' => config('app.name')], $locale) }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;">
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">{{ __('emails.login_new_device.device', [], $locale) }}</td>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 700; text-align: right; color: #111827; font-size: 13px;">{{ $deviceName }}</td>
        </tr>
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">{{ __('emails.login_new_device.location', [], $locale) }}</td>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 700; text-align: right; color: #111827; font-size: 13px;">{{ $location }}</td>
        </tr>
        <tr>
            <td style="padding: 14px 20px; color: #6b7280; font-size: 13px;">{{ __('emails.login_new_device.time', [], $locale) }}</td>
            <td style="padding: 14px 20px; font-weight: 700; text-align: right; color: #111827; font-size: 13px;">{{ $time }}</td>
        </tr>
    </table>

    <p class="text-body" style="color: #374151;">{{ __('emails.login_new_device.safe', [], $locale) }}</p>

    <p class="text-body" style="color: #374151;"><strong>{{ __('emails.login_new_device.warning_lead', [], $locale) }}</strong>{{ __('emails.login_new_device.warning_tail', [], $locale) }}</p>

    @component('emails.components.button', ['url' => $settingsUrl])
        {{ __('emails.login_new_device.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p class="text-body" style="color: #374151;">{{ __('emails.login_new_device.thanks', [], $locale) }}<br>{{ __('emails.login_new_device.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
