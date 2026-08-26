@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">{{ __('emails.order_confirmed.heading', [], $locale) }}</h2>

    <p>{{ __('emails.order_confirmed.greeting', ['name' => $order->customer_name], $locale) }}</p>

    <p>{{ __('emails.order_confirmed.body', ['number' => $order->order_number], $locale) }}</p>

    <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
        @foreach ($order->items as $item)
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                    {{ $item->product_name }} &times; {{ $item->quantity }}
                </td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">
                    {{ number_format($item->price * $item->quantity, 2) }} грн
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding: 12px 0 0 0; color: #718096;">{{ __('emails.order_confirmed.total', [], $locale) }}</td>
            <td style="padding: 12px 0 0 0; font-weight: bold; text-align: right; color: #2d3748;">
                {{ number_format($order->total_price, 2) }} грн
            </td>
        </tr>
    </table>

    <p style="color: #718096;">{{ __('emails.order_confirmed.shipping', [], $locale) }}: {{ $order->shipping_city }}, {{ $order->shipping_address }}</p>

    @component('emails.components.button', ['url' => $accountUrl])
        {{ __('emails.order_confirmed.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p>{{ __('emails.order_confirmed.help', [], $locale) }}</p>

    <p>{{ __('emails.order_confirmed.thanks', [], $locale) }}<br>{{ __('emails.order_confirmed.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
