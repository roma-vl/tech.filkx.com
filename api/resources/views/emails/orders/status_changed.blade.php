@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Статус замовлення оновлено</h2>

    <p>Вітаємо, {{ $order->customer_name }},</p>

    <p>Статус вашого замовлення <strong>№ {{ $order->order_number }}</strong> змінено на:</p>

    <p style="font-size: 18px; font-weight: bold; color: #00a046;">{{ $statusLabel }}</p>

    @if ($order->tracking_number)
        <table width="100%" style="margin: 24px 0; border-collapse: collapse;">
            @if ($order->carrier)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Перевізник</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $order->carrier }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #718096;">Номер відстеження</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; text-align: right; color: #2d3748;">{{ $order->tracking_number }}</td>
            </tr>
        </table>
    @endif

    @component('emails.components.button', ['url' => $accountUrl])
        Переглянути замовлення
    @endcomponent

    @include('emails.components.divider')

    <p>Якщо у вас виникли питання, звертайтесь до нашої служби підтримки.</p>

    <p>Дякуємо, що обрали нас!<br>Команда {{ config('app.name') }}</p>
@endsection
