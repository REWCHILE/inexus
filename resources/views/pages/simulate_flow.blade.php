<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasarela Flow Pagos Chile (Sandbox) | INEXUS Chile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --flow-blue: #0957c3;
            --flow-hover: #0747a1;
            --flow-dark: #0f172a;
            --flow-cyan: #0ea5e9;
            --flow-bg: #f1f5f9;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--flow-bg);
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .flow-container {
            width: 100%;
            max-width: 540px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .flow-header {
            background: linear-gradient(135deg, #0b4fba 0%, #0369a1 100%);
            padding: 28px 24px;
            color: #ffffff;
            text-align: center;
            position: relative;
        }
        .flow-logo-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }
        .flow-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }
        .flow-subtitle {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.85);
        }
        .flow-body {
            padding: 28px 24px;
        }
        .order-meta-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 22px;
        }
        .order-meta-item span {
            display: block;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .order-meta-item strong {
            font-size: 14px;
            color: #0f172a;
        }
        .amount-hero {
            text-align: center;
            margin-bottom: 24px;
            padding: 18px 0;
            border-bottom: 1px dashed #cbd5e1;
            border-top: 1px dashed #cbd5e1;
        }
        .amount-hero-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .amount-hero-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 34px;
            font-weight: 800;
            color: #0b4fba;
            margin-top: 4px;
        }
        .methods-available {
            margin-bottom: 22px;
        }
        .methods-label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .method-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .chip {
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .customer-summary {
            font-size: 13px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .customer-summary-row {
            display: flex;
            justify-content: space-between;
        }
        .btn-flow {
            width: 100%;
            padding: 14px;
            background: #0957c3;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(9, 87, 195, 0.25);
        }
        .btn-flow:hover {
            background: #0747a1;
            box-shadow: 0 6px 16px rgba(9, 87, 195, 0.35);
            transform: translateY(-1px);
        }
        .btn-reject {
            width: 100%;
            padding: 11px;
            background: #fff;
            color: #dc2626;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 13.5px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-reject:hover {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .btn-cancel {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: #64748b;
            font-weight: 500;
            font-size: 13px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 8px;
        }
        .btn-cancel:hover {
            color: #0f172a;
            text-decoration: underline;
        }
        .flow-footer-info {
            margin-top: 20px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="flow-container">
        <div class="flow-header">
            <div class="flow-logo-tag">
                <span>⚡ Gateway Flow.cl</span>
                <span>•</span>
                <span>Sandbox de Pruebas</span>
            </div>
            <h1 class="flow-title">Pasarela de Pagos Flow Chile</h1>
            <p class="flow-subtitle">Portal Seguro de Pago Electrónico Multibanco</p>
        </div>

        <div class="flow-body">
            <div class="order-meta-box">
                <div class="order-meta-item">
                    <span>Comercio Adherido</span>
                    <strong>INEXUS Chile</strong>
                </div>
                <div class="order-meta-item" style="text-align:right;">
                    <span>N° de Orden</span>
                    <strong style="color:#0957c3;">{{ $order->order_number }}</strong>
                </div>
            </div>

            <div class="amount-hero">
                <span class="amount-hero-label">Total a Pagar en Moneda Nacional</span>
                <div class="amount-hero-value">${{ number_format($order->total, 0, ',', '.') }} CLP</div>
            </div>

            <div class="methods-available">
                <div class="methods-label">Medios de Pago Habilitados en Flow:</div>
                <div class="method-chips">
                    <span class="chip">💳 Webpay Plus (Crédito / Débito)</span>
                    <span class="chip">🏦 Redcompra & CuentaRUT</span>
                    <span class="chip">🧾 Servipag Online</span>
                    <span class="chip">📱 Mach</span>
                    <span class="chip">⚡ Khipu</span>
                    <span class="chip">🪙 Multicaja</span>
                </div>
            </div>

            <div class="customer-summary">
                <div class="customer-summary-row">
                    <span style="color:#64748b;">Comprador:</span>
                    <strong>{{ $order->customer_name }}</strong>
                </div>
                <div class="customer-summary-row">
                    <span style="color:#64748b;">Correo electrónico:</span>
                    <strong>{{ $order->customer_email }}</strong>
                </div>
                <div class="customer-summary-row">
                    <span style="color:#64748b;">Documento:</span>
                    <strong>{{ strtoupper($order->document_type) }} ({{ $order->document_type === 'factura' ? $order->company_rut : $order->customer_rut }})</strong>
                </div>
                <div class="customer-summary-row">
                    <span style="color:#64748b;">Destino de despacho:</span>
                    <span>{{ $order->shipping_city }}, {{ $order->shipping_region }}</span>
                </div>
            </div>

            <!-- Approve simulation -->
            <form action="{{ route('checkout.simulate_flow.complete', $order->order_number) }}" method="POST">
                @csrf
                <input type="hidden" name="simulation_status" value="approved">
                <button type="submit" class="btn-flow">
                    ✓ Pagar ${{ number_format($order->total, 0, ',', '.') }} CLP (Simular Pago Exitoso)
                </button>
            </form>

            <!-- Reject simulation -->
            <form action="{{ route('checkout.simulate_flow.complete', $order->order_number) }}" method="POST">
                @csrf
                <input type="hidden" name="simulation_status" value="rejected">
                <button type="submit" class="btn-reject">
                    ✕ Simular Transacción Rechazada
                </button>
            </form>

            <a href="{{ route('checkout.failure', $order->order_number) }}" class="btn-cancel">
                Cancelar transacción y retornar a la tienda
            </a>

            <div class="flow-footer-info">
                🔒 Esta pantalla es el entorno simulado de pruebas de <strong>Flow Chile</strong> en iNexus. Al configurar las llaves en producción o sandbox, el comprador será redirigido automáticamente a la pasarela real de Flow.
            </div>
        </div>
    </div>

</body>
</html>
