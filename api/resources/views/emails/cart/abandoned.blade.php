@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">{{ __('emails.abandoned_cart.heading', [], $locale) }}</h2>

    <p>{{ __('emails.abandoned_cart.greeting', ['name' => $userName], $locale) }}</p>

    <p>{{ __('emails.abandoned_cart.body', [], $locale) }}</p>

    <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
        @foreach ($items as $item)
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #2d3748;">
                    {{ $item['name'] }} &times; {{ $item['quantity'] }}
                </td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">
                    {{ number_format($item['price'] * $item['quantity'], 2) }} грн
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding: 12px 0 0 0; color: #718096;">{{ __('emails.abandoned_cart.total', [], $locale) }}</td>
            <td style="padding: 12px 0 0 0; font-weight: bold; text-align: right; color: #2d3748;">
                {{ number_format($total, 2) }} грн
            </td>
        </tr>
    </table>

    @component('emails.components.button', ['url' => $cartUrl])
        {{ __('emails.abandoned_cart.button', [], $locale) }}
    @endcomponent

    @include('emails.components.divider')

    <p>{{ __('emails.abandoned_cart.help', [], $locale) }}</p>

    <p>{{ __('emails.abandoned_cart.team', ['app' => config('app.name')], $locale) }}</p>
@endsection
