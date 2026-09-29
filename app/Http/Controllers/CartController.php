<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get or initialize session cart
     */
    protected function getCart(): array
    {
        return session()->get('cart', []);
    }

    protected function saveCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart = $this->getCart();
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = round($subtotal * 0.19); // 19% IVA standard Chile
        $shipping = $subtotal > 150000 || empty($cart) ? 0 : 4990; // Free shipping over $150.000 CLP
        $total = $subtotal + $shipping;

        return view('pages.cart', compact('cart', 'subtotal', 'tax', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $product = Product::findOrFail($productId);

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'price' => $product->current_price,
                'image' => $product->image_url,
                'quantity' => $quantity,
                'stock' => $product->stock,
            ];
        }

        $this->saveCart($cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->getDrawerPayload($cart, '¡Producto agregado al carrito con éxito!'));
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito con éxito.');
    }

    public function update(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $this->saveCart($cart);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->getDrawerPayload($cart, 'Carrito actualizado.'));
        }

        return redirect()->route('cart.index')->with('success', 'Carrito actualizado.');
    }

    public function remove(Request $request)
    {
        $productId = $request->input('product_id');
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->getDrawerPayload($cart, 'Producto eliminado del carrito.'));
        }

        return redirect()->route('cart.index')->with('success', 'Producto eliminado.');
    }

    public function drawerHtml(Request $request)
    {
        $cart = $this->getCart();
        return response()->json($this->getDrawerPayload($cart));
    }

    protected function getDrawerPayload(array $cart, string $message = ''): array
    {
        $cartSubtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $shipping = $cartSubtotal > 150000 || empty($cart) ? 0 : 4990;
        $total = $cartSubtotal + $shipping;
        $count = array_sum(array_column($cart, 'quantity'));
        $threshold = 150000;
        $diff = max(0, $threshold - $cartSubtotal);

        return [
            'success' => true,
            'message' => $message,
            'cart_count' => $count,
            'cart_subtotal' => '$' . number_format($cartSubtotal, 0, ',', '.') . ' CLP',
            'shipping' => $shipping === 0 ? 'GRATIS' : '$' . number_format($shipping, 0, ',', '.') . ' CLP',
            'cart_total' => '$' . number_format($total, 0, ',', '.') . ' CLP',
            'free_shipping_diff' => '$' . number_format($diff, 0, ',', '.') . ' CLP',
            'has_free_shipping' => $cartSubtotal >= $threshold,
            'is_empty' => empty($cart),
            'html' => view('partials.cart_drawer_items', compact('cart', 'cartSubtotal', 'total', 'shipping'))->render(),
        ];
    }
}
