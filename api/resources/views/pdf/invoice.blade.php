<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>{{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #2d3748; }
        .header { display: table; width: 100%; margin-bottom: 24px; }
        .header .company { display: table-cell; width: 50%; }
        .header .meta { display: table-cell; width: 50%; text-align: right; }
        .company-name { font-size: 18px; font-weight: bold; }
        h1 { font-size: 20px; margin: 0 0 4px 0; }
        .muted { color: #718096; }
        .parties { display: table; width: 100%; margin-bottom: 24px; }
        .parties .col { display: table-cell; width: 50%; vertical-align: top; }
        .label { font-size: 10px; text-transform: uppercase; color: #a0aec0; font-weight: bold; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { text-align: left; border-bottom: 2px solid #2d3748; padding: 6px 4px; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 6px 4px; border-bottom: 1px solid #e2e8f0; }
        table.items .num { text-align: right; }
        .totals { width: 100%; margin-top: 8px; }
        .totals td { padding: 4px; }
        .totals .num { text-align: right; }
        .totals .grand { font-size: 14px; font-weight: bold; border-top: 2px solid #2d3748; }
        .footer { margin-top: 40px; font-size: 10px; color: #a0aec0; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <div class="company-name">{{ config('app.name') }}</div>
            <div class="muted">ТОВ "ФІЛККС ТЕХНОЛОДЖІ"</div>
        </div>
        <div class="meta">
            <h1>Рахунок / Invoice</h1>
            <div class="muted">№ {{ $order->order_number }}</div>
            <div class="muted">{{ $order->created_at->format('d.m.Y') }}</div>
        </div>
    </div>

    <div class="parties">
        <div class="col">
            <div class="label">Покупець</div>
            <div>{{ $order->customer_name }}</div>
            <div class="muted">{{ $order->customer_email }}</div>
            <div class="muted">{{ $order->customer_phone }}</div>
        </div>
        <div class="col">
            <div class="label">Доставка</div>
            <div>{{ $order->shipping_address }}</div>
            <div class="muted">{{ $order->shipping_city }}, {{ $order->shipping_country }}</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Товар</th>
                <th>SKU</th>
                <th class="num">Ціна</th>
                <th class="num">К-сть</th>
                <th class="num">Сума</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="muted">{{ $item->sku }}</td>
                    <td class="num">{{ number_format($item->price, 2) }} ₴</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ number_format($item->price * $item->quantity, 2) }} ₴</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        @if ($order->discount_amount > 0)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="num muted">Знижка{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</td>
                <td class="num muted">-{{ number_format($order->discount_amount, 2) }} ₴</td>
            </tr>
        @endif
        <tr class="grand">
            <td></td>
            <td></td>
            <td></td>
            <td class="num">До сплати</td>
            <td class="num">{{ number_format($order->total_price, 2) }} ₴</td>
        </tr>
    </table>

    <div class="footer">
        Оплата: {{ $order->payment_method === 'cod' ? 'при отриманні' : ($order->payment_method === 'card' ? 'карткою онлайн' : 'банківський переказ') }}
        &middot; Статус: {{ $order->status }}
    </div>
</body>
</html>
