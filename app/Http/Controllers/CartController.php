<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $cartItems = [];
        
        if ($cart) {
            $cartItems = $cart->items()->with('product')->get();
        }

        return view('cart', compact('cartItems'));
    }

    public function addToCart(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        // Pastikan produk tersedia
        if ($product->status !== 'available') {
            return redirect()->back()->with('error', 'Produk tidak tersedia untuk pembelian.');
        }

        // Cek stok produk
        $quantity = $request->input('quantity', 1);
        if ($product->stock < $quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi. Tersisa: ' . $product->stock . ' unit.');
        }

        // Dapatkan atau buat keranjang untuk user
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['user_id' => Auth::id()]
        );

        // Cari item keranjang atau buat baru
        $cartItem = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $productId,
        ]);

        // Cek total jumlah setelah penambahan
        $newQuantity = $cartItem->exists ? $cartItem->quantity + $quantity : $quantity;
        if ($product->stock < $newQuantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi. Tersisa: ' . $product->stock . ' unit. Saat ini di keranjang: ' . ($cartItem->exists ? $cartItem->quantity : 0) . ' unit.');
        }

        if (!$cartItem->exists) {
            $cartItem->quantity = $quantity;
            $cartItem->save();
        } else {
            $cartItem->increment('quantity', $quantity);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function removeFromCart($itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);
        
        // Pastikan item milik user yang sedang login
        if ($cartItem->cart->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus item ini.');
        }
        
        $cartItem->delete();

        return redirect()->route('cart.index')->with('success', 'Produk berhasil dihapus dari keranjang!');
    }

    public function updateQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999'
        ]);

        $cartItem = CartItem::findOrFail($itemId);

        // Pastikan item milik user yang sedang login
        if ($cartItem->cart->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah jumlah item ini.');
        }

        // Cek ketersediaan stok
        $product = $cartItem->product;
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi. Tersisa: ' . $product->stock . ' unit.');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index')->with('success', 'Jumlah produk berhasil diupdate!');
    }
}