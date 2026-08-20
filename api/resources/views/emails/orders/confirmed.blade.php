@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Дякуємо за замовлення!</h2>

    <p>Вітаємо, {{ $order->customer_name }},</p>

    <p>Ми отримали ваше замовлення <strong>№ {{ $order->order_number }}</strong>. Як тільки статус зміниться, ми
        надішлемо вам повідомлення.</p>

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
            <td style="padding: 12px 0 0 0; color: #718096;">Разом</td>
            <td style="padding: 12px 0 0 0; font-weight: bold; text-align: right; color: #2d3748;">
                {{ number_format($order->total_price, 2) }} грн
            </td>
        </tr>
    </table>

    <p style="color: #718096;">Доставка: {{ $order->shipping_city }}, {{ $order->shipping_address }}</p>

    @component('emails.components.button', ['url' => $accountUrl])
        Переглянути замовлення
    @endcomponent

    @include('emails.components.divider')

    <p>Якщо у вас виникли питання, звертайтесь до нашої служби підтримки.</p>

    <p>Дякуємо, що обрали нас!<br>Команда {{ config('app.name') }}</p>
@endsection
