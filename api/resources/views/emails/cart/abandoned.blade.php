@extends('emails.layouts.system')

@section('content')
    <h2 style="color: #2d3748; margin-top: 0;">Ви залишили товари в кошику</h2>

    <p>Вітаємо, {{ $userName }}!</p>

    <p>Ми помітили, що ви додали товари до кошика, але не завершили оформлення замовлення. Вони
        досі чекають на вас:</p>

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
            <td style="padding: 12px 0 0 0; color: #718096;">Разом</td>
            <td style="padding: 12px 0 0 0; font-weight: bold; text-align: right; color: #2d3748;">
                {{ number_format($total, 2) }} грн
            </td>
        </tr>
    </table>

    @component('emails.components.button', ['url' => $cartUrl])
        Повернутися до кошика
    @endcomponent

    @include('emails.components.divider')

    <p>Якщо у вас виникли питання, звертайтесь до нашої служби підтримки.</p>

    <p>Команда {{ config('app.name') }}</p>
@endsection
