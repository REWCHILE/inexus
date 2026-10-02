<?php

namespace Tests\Unit;

use App\Models\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function test_payment_method_name_attribute(): void
    {
        $order = new Order();

        $order->payment_method = 'flow';
        $this->assertEquals('Flow Chile (Webpay / Tarjetas)', $order->payment_method_name);

        $order->payment_method = 'mercadopago';
        $this->assertEquals('Mercado Pago', $order->payment_method_name);

        $order->payment_method = 'transferencia';
        $this->assertEquals('Transferencia Bancaria Directa', $order->payment_method_name);
    }
}
