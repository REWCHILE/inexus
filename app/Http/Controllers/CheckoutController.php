<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected MercadoPagoService $mercadoPago;

    public function __construct(MercadoPagoService $mercadoPago)
    {
        $this->mercadoPago = $mercadoPago;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('warning', 'Tu carrito está vacío. Agrega productos para iniciar el checkout.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = $subtotal > 150000 ? 0 : 4990;
        $total = $subtotal + $shipping;

        return view('pages.checkout', compact('cart', 'subtotal', 'shipping', 'total'));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'El carrito está vacío.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_rut' => 'required|string|max:20',
            'document_type' => 'required|in:boleta,factura',
            'company_name' => 'nullable|required_if:document_type,factura|string|max:150',
            'company_rut' => 'nullable|required_if:document_type,factura|string|max:20',
            'company_giro' => 'nullable|required_if:document_type,factura|string|max:150',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_region' => 'required|string|max:100',
            'payment_method' => 'required|in:mercadopago,transferencia',
        ]);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = $subtotal > 150000 ? 0 : 4990;
        $total = $subtotal + $shipping;
        $tax = round($subtotal * 0.19);

        $orderNumber = 'INX-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_rut' => $request->customer_rut,
            'document_type' => $request->document_type,
            'company_name' => $request->company_name,
            'company_rut' => $request->company_rut,
            'company_giro' => $request->company_giro,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_region' => $request->shipping_region,
            'shipping_notes' => $request->shipping_notes,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_cost' => $shipping,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'product_name' => $item['name'],
                'product_sku' => $item['sku'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);

            // Decrement product stock
            Product::where('id', $productId)->decrement('stock', $item['quantity']);
        }

        // Clear cart from session
        session()->forget('cart');

        if ($request->payment_method === 'mercadopago') {
            $preference = $this->mercadoPago->createPreference($order);
            if (!empty($preference['init_point'])) {
                return redirect()->away($preference['init_point']);
            }
        }

        // If transferencia or fallback
        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', '¡Pedido registrado con éxito!');
    }

    public function simulateMp(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('pages.simulate_mp', compact('order'));
    }

    public function completeSimulatedMp(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->payment_status = 'approved';
        $order->payment_id = 'MP-' . rand(100000000, 999999999);
        $order->status = 'processing';
        $order->save();

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', '¡Pago procesado exitosamente por Mercado Pago!');
    }

    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();

        return view('pages.order_confirmation', compact('order'));
    }

    public function success(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->payment_status = 'approved';
        $order->payment_id = $request->get('payment_id', 'MP-ONLINE-' . rand(100000, 999999));
        $order->status = 'processing';
        $order->save();

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', '¡Pago con Mercado Pago recibido y confirmado!');
    }

    public function pending(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->payment_status = 'pending';
        $order->save();

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('warning', 'Tu pago se encuentra en proceso de validación.');
    }

    public function failure(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->payment_status = 'rejected';
        $order->save();

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('error', 'El pago fue rechazado. Puedes reintentar con otro medio de pago.');
    }
}
