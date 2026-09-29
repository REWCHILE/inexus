<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\BlueExpressService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected MercadoPagoService $mercadoPago;
    protected BlueExpressService $blueExpress;

    public function __construct(MercadoPagoService $mercadoPago, BlueExpressService $blueExpress)
    {
        $this->mercadoPago = $mercadoPago;
        $this->blueExpress = $blueExpress;
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

        $regions = $this->blueExpress->getRegions();
        $initialRegion = 'CL-RM';
        $communes = $this->blueExpress->getCommunesByRegion($initialRegion);
        $initialCommune = 'Santiago';

        $quote = $this->blueExpress->quoteShipping($initialRegion, $initialCommune, $cart, $subtotal);
        $shipping = $quote['cost'];
        $shippingCourier = $quote['courier'];
        $shippingPromise = $quote['promise_day'];
        $shippingServiceName = $quote['service_name'];
        $isFreeShipping = $quote['is_free'];

        $total = $subtotal + $shipping;

        return view('pages.checkout', compact(
            'cart', 'subtotal', 'shipping', 'total',
            'regions', 'communes', 'initialRegion', 'initialCommune',
            'shippingCourier', 'shippingPromise', 'shippingServiceName', 'isFreeShipping'
        ));
    }

    public function getCommunes(string $regionCode)
    {
        $communes = $this->blueExpress->getCommunesByRegion($regionCode);
        return response()->json([
            'success' => true,
            'communes' => $communes,
        ]);
    }

    public function quoteShipping(Request $request)
    {
        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $regionCode = $request->input('region_code', 'CL-RM');
        $communeName = $request->input('commune_name', 'Santiago');
        $paymentMethod = $request->input('payment_method', 'mercadopago');

        $quote = $this->blueExpress->quoteShipping($regionCode, $communeName, $cart, $subtotal);

        $discount = 0;
        if ($paymentMethod === 'transferencia') {
            $discount = round($subtotal * 0.05);
        }

        $shippingCost = $quote['cost'];
        $total = ($subtotal - $discount) + $shippingCost;

        return response()->json([
            'success' => true,
            'courier' => $quote['courier'],
            'service_name' => $quote['service_name'],
            'promise_day' => $quote['promise_day'],
            'shipping_cost' => $shippingCost,
            'shipping_formatted' => $shippingCost === 0 ? 'GRATIS' : '$' . number_format($shippingCost, 0, ',', '.') . ' CLP',
            'is_free' => $quote['is_free'],
            'discount' => $discount,
            'discount_formatted' => '-$' . number_format($discount, 0, ',', '.') . ' CLP',
            'subtotal' => $subtotal,
            'subtotal_formatted' => '$' . number_format($subtotal, 0, ',', '.') . ' CLP',
            'total' => $total,
            'total_formatted' => '$' . number_format($total, 0, ',', '.') . ' CLP',
        ]);
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
            'shipping_region_code' => 'nullable|string|max:10',
            'shipping_region' => 'nullable|string|max:100',
            'payment_method' => 'required|in:mercadopago,transferencia',
        ]);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $regionCode = $request->input('shipping_region_code', 'CL-RM');
        $communeName = $request->input('shipping_city', 'Santiago');

        // Real-time Blue Express rate calculation
        $quote = $this->blueExpress->quoteShipping($regionCode, $communeName, $cart, $subtotal);
        $shipping = $quote['cost'];
        $discount = 0;

        if ($request->payment_method === 'transferencia') {
            $discount = round($subtotal * 0.05);
        }

        $total = ($subtotal - $discount) + $shipping;
        $tax = round($subtotal * 0.19);

        $regions = $this->blueExpress->getRegions();
        $regionName = $regions[$regionCode]['name'] ?? ($request->shipping_region ?? 'Región Metropolitana de Santiago');

        $orderNumber = 'INX-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

        $notesArray = [];
        if ($discount > 0) {
            $notesArray[] = "Descuento 5% aplicado por Transferencia Electrónica Directa (-$" . number_format($discount, 0, ',', '.') . " CLP)";
        }
        $notesArray[] = "Courier: Blue Express ({$quote['service_name']}) - Tiempo estimado: {$quote['promise_day']}";

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
            'shipping_city' => $communeName,
            'shipping_region' => $regionName,
            'shipping_notes' => $request->shipping_notes,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_cost' => $shipping,
            'total' => $total,
            'status' => 'pending',
            'notes' => implode(" | ", $notesArray),
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
