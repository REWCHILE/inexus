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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Producto agregado al carrito!',
                'cart_count' => array_sum(array_column($cart, 'quantity')),
                'cart_subtotal' => '$' . number_format(array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart)), 0, ',', '.') . ' CLP',
            ]);
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

        if ($request->wantsJson()) {
            $itemSubtotal = ($cart[$productId]['price'] ?? 0) * $quantity;
            $cartSubtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
            $shipping = $cartSubtotal > 150000 || empty($cart) ? 0 : 4990;
            $total = $cartSubtotal + $shipping;

            return response()->json([
                'success' => true,
                'item_subtotal' => '$' . number_format($itemSubtotal, 0, ',', '.') . ' CLP',
                'cart_count' => array_sum(array_column($cart, 'quantity')),
                'cart_subtotal' => '$' . number_format($cartSubtotal, 0, ',', '.') . ' CLP',
                'shipping' => '$' . number_format($shipping, 0, ',', '.') . ' CLP',
                'cart_total' => '$' . number_format($total, 0, ',', '.') . ' CLP',
            ]);
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

        if ($request->wantsJson()) {
            $cartSubtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
            $shipping = $cartSubtotal > 150000 || empty($cart) ? 0 : 4990;
            $total = $cartSubtotal + $shipping;

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito.',
                'cart_count' => array_sum(array_column($cart, 'quantity')),
                'cart_subtotal' => '$' . number_format($cartSubtotal, 0, ',', '.') . ' CLP',
                'cart_total' => '$' . number_format($total, 0, ',', '.') . ' CLP',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Producto eliminado.');
    }
}
