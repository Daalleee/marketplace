<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Message;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function checkout()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $cartItems = [];
        $totalAmount = 0;
        
        if ($cart) {
            $cartItems = $cart->items()->with('product')->get();
            foreach ($cartItems as $item) {
                $totalAmount += $item->quantity * $item->product->price;
            }
        }

        return view('checkout', compact('cartItems', 'totalAmount'));
    }

    public function processCheckout(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $totalAmount = 0;
        foreach ($cart->items as $item) {
            $totalAmount += $item->quantity * $item->product->price;
        }

        // Buat nomor pesanan unik
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        // Buat pesanan
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        // Pindahkan item dari keranjang ke pesanan
        $productSellers = []; // Untuk menyimpan penjual dari setiap produk
        foreach ($cart->items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price
            ]);

            // Simpan ID penjual
            $productSellers[] = $cartItem->product->user_id;
        }

        // Kosongkan keranjang
        $cart->items()->delete();

        // Kirim pesan otomatis ke semua penjual produk di keranjang
        foreach ($productSellers as $sellerId) {
            if ($sellerId != Auth::id()) { // Pastikan pembeli tidak mengirim pesan ke diri sendiri
                Message::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $sellerId,
                    'order_id' => $order->id,
                    'message' => "Halo, saya baru saja memesan produk Anda. Nomor pesanan: {$orderNumber}. Mohon bantuannya untuk proses selanjutnya.",
                    'is_read' => false
                ]);
            }
        }

        // Buat transaksi Midtrans
        $midtransService = new MidtransService();

        // Detail pelanggan
        $customerDetails = [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ];

        // Detail item
        $itemDetails = [];
        foreach ($cart->items as $item) {
            $itemDetails[] = [
                'id' => $item->product_id,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'name' => $item->product->name,
            ];
        }

        try {
            // Buat order ID unik untuk Midtrans (gunakan timestamp untuk memastikan unik)
            $midtransOrderId = $orderNumber . '-' . time();

            $snapToken = $midtransService->createTransaction(
                $midtransOrderId,
                $totalAmount,
                $customerDetails,
                $itemDetails
            );

            // Update status order ke pending pembayaran dan tambahkan midtrans_order_id
            $order->update([
                'status' => 'waiting_payment',
                'midtrans_order_id' => $midtransOrderId
            ]);

            // Redirect ke halaman pembayaran
            return view('orders.payment', compact('order', 'snapToken'));

        } catch (\Exception $e) {
            // Jika Midtrans gagal, kembalikan status produk ke available
            foreach ($cart->items as $cartItem) {
                $cartItem->product->update(['status' => 'available']);
            }

            // Hapus order jika pembuatan Midtrans gagal
            $order->delete();

            return redirect()->route('cart.index')
                ->with('error', 'Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }
    }
}