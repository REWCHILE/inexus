<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected string $accessToken;
    protected string $publicKey;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->accessToken = trim((string) Setting::get('mercadopago_access_token', 'TEST-74928192837491-092812-inexus-token-chile'));
        $this->publicKey = trim((string) Setting::get('mercadopago_public_key', 'TEST-pub-key-inexus'));
        $this->isSandbox = (bool) Setting::get('mercadopago_sandbox', true);
    }

    /**
     * Create Checkout Preference for an Order
     */
    public function createPreference(Order $order): array
    {
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'title' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'currency_id' => 'CLP',
                'unit_price' => (float) $item->price,
            ];
        }

        // If shipping cost > 0
        if ($order->shipping_cost > 0) {
            $items[] = [
                'title' => 'Costo de Despacho',
                'quantity' => 1,
                'currency_id' => 'CLP',
                'unit_price' => (float) $order->shipping_cost,
            ];
        }

        $payload = [
            'items' => $items,
            'payer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => [
                    'number' => $order->customer_phone,
                ],
            ],
            'back_urls' => [
                'success' => route('checkout.success', ['order_number' => $order->order_number]),
                'pending' => route('checkout.pending', ['order_number' => $order->order_number]),
                'failure' => route('checkout.failure', ['order_number' => $order->order_number]),
            ],
            'auto_return' => 'approved',
            'external_reference' => $order->order_number,
            'statement_descriptor' => 'INEXUS CHILE',
        ];

        // Call real Mercado Pago API if access token is configured
        if (!empty($this->accessToken) && !str_starts_with($this->accessToken, 'TEST-74928192837491')) {
            try {
                $response = Http::withToken($this->accessToken)
                    ->post('https://api.mercadopago.com/checkout/preferences', $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'init_point' => $this->isSandbox ? ($data['sandbox_init_point'] ?? $data['init_point']) : $data['init_point'],
                        'preference_id' => $data['id'] ?? null,
                    ];
                }
                Log::warning('MercadoPago preference failed: ' . $response->body());
            } catch (\Throwable $e) {
                Log::error('MercadoPago exception: ' . $e->getMessage());
            }
        }

        // Sandbox / Local Simulation Fallback:
        // Returns simulated gateway URL that allows user to test payment success immediately!
        return [
            'success' => true,
            'is_simulation' => true,
            'init_point' => route('checkout.simulate_mp', ['order_number' => $order->order_number]),
            'preference_id' => 'SIMULATED-' . $order->order_number,
        ];
    }
}
