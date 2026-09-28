<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercado Pago Sandbox Checkout | INEXUS Chile</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Jost', sans-serif;
            background: #f5f5f5;
            color: #333333;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .mp-box {
            background: #ffffff;
            width: 100%;
            max-width: 520px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            margin: 20px;
        }
        .mp-header {
            background: #009ee3;
            padding: 24px;
            color: #ffffff;
            text-align: center;
        }
        .mp-badge {
            background: rgba(255,255,255,0.25);
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .mp-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 800;
            margin: 0;
        }
        .mp-body {
            padding: 28px;
        }
        .order-badge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid #eeeeee;
            margin-bottom: 20px;
        }
        .amount-big {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            margin: 10px 0 20px;
        }
        .btn-mp {
            display: block;
            width: 100%;
            padding: 14px;
            background: #009ee3;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-mp:hover {
            background: #0081bb;
        }
        .btn-cancel {
            display: block;
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 13.5px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
        }
        .btn-cancel:hover {
            color: #ef4444;
        }
    </style>
</head>
<body>

    <div class="mp-box">
        <div class="mp-header">
            <span class="mp-badge">Ambiente de Pruebas Sandbox</span>
            <h1 class="mp-title">Mercado Pago Chile</h1>
        </div>

        <div class="mp-body">
            <div class="order-badge-row">
                <div>
                    <span style="font-size:12px; color:#64748b; display:block;">Comercio:</span>
                    <strong>INEXUS Chile Hardware</strong>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:12px; color:#64748b; display:block;">N° Pedido:</span>
                    <strong style="color:#009ee3;">{{ $order->order_number }}</strong>
                </div>
            </div>

            <div style="text-align:center;">
                <span style="font-size:13px; color:#64748b;">Monto total a procesar en Pesos Chilenos:</span>
                <div class="amount-big">${{ number_format($order->total, 0, ',', '.') }} CLP</div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:24px; font-size:13.5px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#64748b;">Cliente:</span>
                    <strong>{{ $order->customer_name }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#64748b;">Documento:</span>
                    <strong>{{ strtoupper($order->document_type) }} ({{ $order->document_type === 'factura' ? $order->company_rut : $order->customer_rut }})</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#64748b;">Despacho:</span>
                    <span>{{ $order->shipping_city }}, {{ $order->shipping_region }}</span>
                </div>
            </div>

            <form action="{{ route('checkout.simulate_mp.complete', $order->order_number) }}" method="POST">
                @csrf
                <button type="submit" class="btn-mp">
                    ✓ Pagar ${{ number_format($order->total, 0, ',', '.') }} CLP (Simular Aprobado)
                </button>
            </form>

            <a href="{{ route('checkout.failure', $order->order_number) }}" class="btn-cancel">
                Cancelar transacción y volver
            </a>
        </div>
    </div>

</body>
</html>
