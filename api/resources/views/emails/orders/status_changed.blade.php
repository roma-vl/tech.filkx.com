@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">{{ __('emails.order_status_changed.heading', [], $locale) }}</h2>

    <p>{{ __('emails.order_status_changed.greeting', ['name' => $order->customer_name], $locale) }}</p>

    <p>{{ __('emails.order_status_changed.body', ['number' => $order->order_number], $locale) }}</p>

    <p style="font-size: 18px; font-weight: bold; color: #00a046;">{{ $statusLabel }}</p>

    @if ($order->tracking_number)
        <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
            @if ($order->carrier)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">{{ __('emails.order_status_changed.carrier', [], $locale) }}</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $order->carrier }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">{{ __('emails.order_status_changed.tracking_number', [], $locale) }}</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $order->tracking_number }}</td>
            </tr>
        </table>
    @endif

    @component('emails.components.button', ['url' => $accountUrl])
        {{ __('emails.order_status_changed.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p>{{ __('emails.order_status_changed.help', [], $locale) }}</p>

    <p>{{ __('emails.order_status_changed.thanks', [], $locale) }}<br>{{ __('emails.order_status_changed.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
