<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\BlueExpressService;
use App\Services\FlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected BlueExpressService $blueExpress;
    protected FlowService $flow;

    public function __construct(
        BlueExpressService $blueExpress,
        FlowService $flow
    ) {
        $this->blueExpress = $blueExpress;
        $this->flow = $flow;
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

        $quoteDomicilio = $this->blueExpress->quoteShipping($initialRegion, $initialCommune, $cart, $subtotal, 'domicilio');
        $quotePickup = $this->blueExpress->quoteShipping($initialRegion, $initialCommune, $cart, $subtotal, 'pickup');

        $initialShippingType = 'domicilio';
        $shipping = $quoteDomicilio['cost'];
        $shippingCourier = $quoteDomicilio['courier'];
        $shippingPromise = $quoteDomicilio['promise_day'];
        $shippingServiceName = $quoteDomicilio['service_name'];
        $isFreeShipping = $quoteDomicilio['is_free'];

        $total = $subtotal + $shipping;

        return view('pages.checkout', compact(
            'cart', 'subtotal', 'shipping', 'total',
            'regions', 'communes', 'initialRegion', 'initialCommune',
            'shippingCourier', 'shippingPromise', 'shippingServiceName', 'isFreeShipping',
            'initialShippingType', 'quoteDomicilio', 'quotePickup'
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
        $paymentMethod = $request->input('payment_method', 'flow');
        $shippingType = $request->input('shipping_type', 'domicilio');

        $quote = $this->blueExpress->quoteShipping($regionCode, $communeName, $cart, $subtotal, $shippingType);

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
            'shipping_type' => $quote['shipping_type'] ?? $shippingType,
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
            'customer_first_name' => 'nullable|string|max:80',
            'customer_last_name' => 'nullable|string|max:80',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_rut' => 'required|string|max:20',
            'document_type' => 'required|in:boleta,factura',
            'company_name' => 'nullable|required_if:document_type,factura|string|max:150',
            'company_rut' => 'nullable|required_if:document_type,factura|string|max:20',
            'company_giro' => 'nullable|required_if:document_type,factura|string|max:150',
            'shipping_type' => 'required|in:domicilio,pickup',
            'shipping_address' => 'required_if:shipping_type,domicilio|nullable|string|max:255',
            'agency_name' => 'required_if:shipping_type,pickup|nullable|string|max:255',
            'agency_address' => 'nullable|string|max:255',
            'agency_id' => 'nullable|string|max:50',
            'shipping_city' => 'required|string|max:100',
            'shipping_region_code' => 'nullable|string|max:10',
            'shipping_region' => 'nullable|string|max:100',
            'payment_method' => 'required|in:flow,transferencia',
        ]);

        $customerName = trim(($request->customer_first_name ?? '') . ' ' . ($request->customer_last_name ?? ''));
        if (empty($customerName)) {
            $customerName = trim((string) $request->customer_name);
        }
        if (empty($customerName)) {
            return back()->withErrors(['customer_first_name' => 'Por favor ingrese su Nombre y Apellido.'])->withInput();
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $regionCode = $request->input('shipping_region_code', 'CL-RM');
        $communeName = $request->input('shipping_city', 'Santiago');
        $shippingType = $request->input('shipping_type', 'domicilio');

        // Real-time Blue Express rate calculation
        $quote = $this->blueExpress->quoteShipping($regionCode, $communeName, $cart, $subtotal, $shippingType);
        $shipping = $quote['cost'];
        $discount = 0;

        if ($request->payment_method === 'transferencia') {
            $discount = round($subtotal * 0.05);
        }

        $total = ($subtotal - $discount) + $shipping;
        $tax = round($subtotal * 0.19);

        $regions = $this->blueExpress->getRegions();
        $regionName = $regions[$regionCode]['name'] ?? ($request->shipping_region ?? 'Región Metropolitana de Santiago');

        $finalShippingAddress = $request->shipping_address;
        if ($shippingType === 'pickup') {
            $finalShippingAddress = 'Retiro en Punto Blue: ' . $request->agency_name . ($request->agency_address ? ' (' . $request->agency_address . ')' : '');
        }

        $orderNumber = 'INX-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

        $notesArray = [];
        if ($discount > 0) {
            $notesArray[] = "Descuento 5% aplicado por Transferencia Electrónica Directa (-$" . number_format($discount, 0, ',', '.') . " CLP)";
        }
        if ($shippingType === 'pickup') {
            $notesArray[] = "Modalidad: Retiro en Punto Blue Express ({$request->agency_name}) - ID Agencia: {$request->agency_id} - Dirección Punto: {$request->agency_address}";
        } else {
            $notesArray[] = "Modalidad: Envío a Domicilio - Courier: Blue Express ({$quote['service_name']}) - Tiempo estimado: {$quote['promise_day']}";
        }

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $customerName,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_rut' => $request->customer_rut,
            'document_type' => $request->document_type,
            'company_name' => $request->company_name,
            'company_rut' => $request->company_rut,
            'company_giro' => $request->company_giro,
            'shipping_address' => $finalShippingAddress,
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

        if ($request->payment_method === 'flow') {
            $flowPayment = $this->flow->createPayment($order);
            if (!empty($flowPayment['redirect_url'])) {
                return redirect()->away($flowPayment['redirect_url']);
            }
        }

        // If transferencia or fallback
        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', '¡Pedido registrado con éxito!');
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

    /**
     * Flow Sandbox Simulation Page
     */
    public function simulateFlow(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('pages.simulate_flow', compact('order'));
    }

    /**
     * Complete Flow Simulated Sandbox Payment
     */
    public function completeSimulatedFlow(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $status = $request->input('simulation_status', 'approved');

        if ($status === 'rejected') {
            $order->payment_status = 'rejected';
            $order->save();

            return redirect()->route('checkout.failure', $order->order_number)
                ->with('error', 'El pago fue cancelado o rechazado en la pasarela Flow.');
        }

        $order->payment_status = 'approved';
        $order->payment_id = 'FLOW-' . rand(1000000, 9999999);
        $order->status = 'processing';
        $order->save();

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', '¡Pago procesado exitosamente a través de Flow (Webpay Plus / Multibanco)!');
    }

    /**
     * Flow Customer Return URL (Flow redirects user browser here)
     */
    public function flowReturn(Request $request)
    {
        $token = $request->input('token');

        if (empty($token)) {
            Log::warning('Flow Return called without token.');
            return redirect()->route('shop.index')
                ->with('warning', 'No se recibió comprobante de pago de Flow.');
        }

        $result = $this->flow->getPaymentStatus($token);

        if (!$result['success'] || empty($result['data'])) {
            Log::error('Flow Return: Failed to get payment status for token: ' . $token);
            return redirect()->route('shop.index')
                ->with('error', 'No fue posible validar el estado del pago con Flow.');
        }

        $data = $result['data'];
        $commerceOrder = $data['commerceOrder'] ?? null;
        $order = Order::where('order_number', $commerceOrder)->first();

        if (!$order) {
            Log::error('Flow Return: Order not found for commerceOrder: ' . $commerceOrder);
            return redirect()->route('shop.index')
                ->with('error', 'Orden no encontrada en nuestro sistema.');
        }

        // Flow Status: 1 = Pendiente, 2 = Pagada, 3 = Rechazada, 4 = Anulada
        $status = (int) ($data['status'] ?? 0);
        $flowOrder = $data['flowOrder'] ?? $token;

        if ($status === 2) {
            $order->payment_status = 'approved';
            $order->payment_id = 'FLOW-' . $flowOrder;
            $order->status = 'processing';
            $order->save();

            return redirect()->route('order.confirmation', $order->order_number)
                ->with('success', '¡Pago procesado exitosamente a través de Flow!');
        } elseif ($status === 1) {
            $order->payment_status = 'pending';
            $order->payment_id = 'FLOW-' . $flowOrder;
            $order->save();

            return redirect()->route('order.confirmation', $order->order_number)
                ->with('warning', 'Tu pago se encuentra en proceso de validación por Flow.');
        } else {
            $order->payment_status = 'rejected';
            $order->save();

            return redirect()->route('checkout.failure', $order->order_number)
                ->with('error', 'El pago fue rechazado o anulado en la plataforma de Flow.');
        }
    }

    /**
     * Flow Server-to-Server Confirmation Webhook
     */
    public function flowConfirm(Request $request)
    {
        $token = $request->input('token');

        if (empty($token)) {
            Log::warning('Flow Confirm webhook called without token.');
            return response('Token missing', 400);
        }

        $result = $this->flow->getPaymentStatus($token);

        if (!$result['success'] || empty($result['data'])) {
            Log::error('Flow Confirm Webhook: Failed to verify payment for token: ' . $token);
            return response('Payment verification failed', 500);
        }

        $data = $result['data'];
        $commerceOrder = $data['commerceOrder'] ?? null;
        $order = Order::where('order_number', $commerceOrder)->first();

        if ($order) {
            $status = (int) ($data['status'] ?? 0);
            $flowOrder = $data['flowOrder'] ?? $token;

            if ($status === 2) {
                $order->payment_status = 'approved';
                $order->payment_id = 'FLOW-' . $flowOrder;
                $order->status = 'processing';
                $order->save();
                Log::info("Flow Webhook: Order {$order->order_number} marked as approved.");
            } elseif ($status === 3 || $status === 4) {
                $order->payment_status = 'rejected';
                $order->save();
                Log::info("Flow Webhook: Order {$order->order_number} marked as rejected.");
            }
        }

        return response('OK', 200);
    }
}
