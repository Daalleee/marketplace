<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MidtransController;

// Halaman Utama
Route::get('/', [ProductController::class, 'index'])->name('home');


// Autentikasi
Auth::routes();

// Marketplace (publik)
Route::get('/marketplace', [ProductController::class, 'index'])->name('marketplace');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');

// Middleware untuk rute yang membutuhkan login
Route::middleware(['auth'])->group(function () {
    // Jual Barang
    Route::get('/jual-barang', [ProductController::class, 'create'])->name('sell.create');
    Route::post('/jual-barang', [ProductController::class, 'store'])->name('sell.store');
    Route::get('/produk/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('/produk/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/produk/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

    // Beli Sekarang
    Route::post('/product/{id}/buy-now', [ProductController::class, 'buyNow'])->name('product.buy-now');

    // Review Produk
    Route::post('/product/{id}/review', [ProductController::class, 'submitReview'])->name('product.review');

    // Keranjang
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::put('/cart/update/{itemId}', [CartController::class, 'updateQuantity'])->name('cart.update');

    // Checkout
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');

    // Pesanan
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Pesan/Chat - Menggunakan pendekatan closure untuk menghindari masalah resolution
    Route::middleware(['auth'])->group(function () {
        Route::get('/messages', function () {
            return app()->call('App\Http\Controllers\MessageController@index');
        })->name('messages.index');

        Route::get('/messages/{userId}', function ($userId) {
            return app()->call('App\Http\Controllers\MessageController@chat', ['userId' => $userId]);
        })->name('messages.chat');

        Route::post('/messages/{userId}', function (\Illuminate\Http\Request $request, $userId) {
            return app()->call('App\Http\Controllers\MessageController@send', [
                'request' => $request,
                'userId' => $userId
            ]);
        })->name('messages.send');

        // Mendapatkan jumlah pesan belum dibaca
        Route::get('/messages/unread-count', function () {
            return app()->call('App\Http\Controllers\MessageController@getUnreadCount');
        })->name('messages.unread.count');

        // Pembayaran pesanan
        Route::get('/orders/{orderId}/payment', function ($orderId) {
            $order = \App\Models\Order::where('user_id', auth()->id())->findOrFail($orderId);
            $midtransService = new \App\Services\MidtransService();

            $customerDetails = [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ];

            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product->name,
                ];
            }

            // Buat order ID unik untuk Midtrans (gunakan timestamp untuk memastikan unik)
            $midtransOrderId = $order->order_number . '-' . time();

            $snapToken = $midtransService->createTransaction(
                $midtransOrderId,
                $order->total_amount,
                $customerDetails,
                $itemDetails
            );

            // Update order dengan midtrans_order_id yang baru
            $order->update([
                'midtrans_order_id' => $midtransOrderId,
                'status' => 'waiting_payment'
            ]);

            return view('orders.payment', compact('order', 'snapToken'));
        })->name('orders.payment');
    });

    // Profil User
    Route::get('/profile', [UserController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');
});

// Callback Midtrans - tidak menggunakan middleware auth
Route::post('/midtrans/callback', [MidtransController::class, 'handleNotification']);
