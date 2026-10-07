@php
    $address = trim($order->shipping_address.', '.$order->shipping_city.($order->shipping_postal_code ? ' '.$order->shipping_postal_code : ''));
    $paymentMethod = $order->payment_method === 'cash_on_delivery'
        ? 'Pagesë me para në dorëzim'
        : ($order->payment_method ?: 'N/A');
@endphp

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <title>Konfirmimi i porosisë {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111111; line-height: 1.5;">
    <h1 style="font-size: 22px;">Faleminderit për porosinë tuaj!</h1>

    <p>Përshëndetje {{ $order->customer_name }},</p>
    <p>Porosia juaj në DenataShop u pranua me sukses. Më poshtë i gjeni detajet e porosisë.</p>

    <h2 style="font-size: 16px;">Porosia {{ $order->order_number }}</h2>
    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr><td><strong>Data:</strong></td><td>{{ optional($order->created_at)->format('d.m.Y H:i') }}</td></tr>
        <tr><td><strong>Adresa e dorëzimit:</strong></td><td>{{ $address }}</td></tr>
        <tr><td><strong>Telefoni:</strong></td><td>{{ $order->customer_phone }}</td></tr>
        <tr><td><strong>Mënyra e pagesës:</strong></td><td>{{ $paymentMethod }}</td></tr>
    </table>

    <h2 style="font-size: 16px;">Produktet e porositura</h2>
    <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #e5e7eb;">
        <thead>
            <tr style="background: #f7f6f3;">
                <th align="left" style="border: 1px solid #e5e7eb;">Produkti</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Sasia</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Çmimi</th>
                <th align="right" style="border: 1px solid #e5e7eb;">Totali</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td style="border: 1px solid #e5e7eb;">{{ $item->product_name }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">{{ $item->quantity }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">€{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td align="right" style="border: 1px solid #e5e7eb;">€{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="right" style="border: 1px solid #e5e7eb;"><strong>Totali:</strong></td>
                <td align="right" style="border: 1px solid #e5e7eb;"><strong>€{{ number_format((float) $order->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p>Ekipi ynë do t’ju kontaktojë nëse nevojiten informata shtesë për dorëzim.</p>
    <p>Faleminderit që zgjodhët DenataShop.</p>
</body>
</html>
