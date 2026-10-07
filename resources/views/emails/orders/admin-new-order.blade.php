@php
    $address = trim($order->shipping_address.', '.$order->shipping_city.($order->shipping_postal_code ? ' '.$order->shipping_postal_code : ''));
@endphp

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <title>Porosi e re - {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111111; line-height: 1.5;">
    <h1 style="font-size: 22px;">Porosi e re - {{ $order->order_number }}</h1>

    <p>U krijua një porosi e re në DenataShop.</p>

    <h2 style="font-size: 16px;">Detajet e porosisë</h2>
    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr><td><strong>Numri i porosisë:</strong></td><td>{{ $order->order_number }}</td></tr>
        <tr><td><strong>Data:</strong></td><td>{{ optional($order->created_at)->format('d.m.Y H:i') }}</td></tr>
        <tr><td><strong>Klienti:</strong></td><td>{{ $order->customer_name }}</td></tr>
        <tr><td><strong>Email:</strong></td><td>{{ $order->customer_email }}</td></tr>
        <tr><td><strong>Telefoni:</strong></td><td>{{ $order->customer_phone }}</td></tr>
        <tr><td><strong>Adresa:</strong></td><td>{{ $address }}</td></tr>
        <tr><td><strong>Pagesa:</strong></td><td>{{ $order->payment_method ?: 'N/A' }}</td></tr>
    </table>

    <h2 style="font-size: 16px;">Produktet</h2>
    <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #e5e7eb;">
        <thead>
            <tr style="background: #f7f6f3;">
                <th align="left" style="border: 1px solid #e5e7eb;">Produkti</th>
                <th align="left" style="border: 1px solid #e5e7eb;">SKU</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Sasia</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Çmimi</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Totali</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td style="border: 1px solid #e5e7eb;">{{ $item->product_name }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $item->product_sku }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">{{ $item->quantity }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">€{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">€{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" align="right" style="border: 1px solid #e5e7eb;"><strong>Totali:</strong></td>
                <td align="right" style="border: 1px solid #e5e7eb;"><strong>€{{ number_format((float) $order->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if ($order->notes)
        <h2 style="font-size: 16px;">Shënime</h2>
        <p>{{ $order->notes }}</p>
    @endif
</body>
</html>
