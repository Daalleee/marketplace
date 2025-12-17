<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function handleNotification(Request $request)
    {
        try {
            // Ambil JSON dari body request
            $json = $request->getContent();
            $notification = json_decode($json, true); // Gunakan true untuk mengembalikan array bukan objek

            // Jika decode gagal atau bukan array, coba dengan input()
            if (is_null($notification) || !is_array($notification)) {
                $notification = $request->json()->all();
            }

            // Jika masih null, coba ambil dari input biasa (untuk debugging)
            if (is_null($notification) || !is_array($notification)) {
                $notification = $request->all();
            }

            // Pastikan kita memiliki data yang dibutuhkan
            if (!isset($notification['order_id']) || !isset($notification['transaction_status'])) {
                Log::error('Invalid notification data received:', $notification);
                return response('Invalid notification data', 400);
            }

            $order_id = $notification['order_id'];
            $transaction_status = $notification['transaction_status'];
            $fraud_status = $notification['fraud_status'] ?? 'accept'; // Beri default jika tidak ada

            Log::info('Midtrans Notification:', [
                'order_id' => $order_id,
                'transaction_status' => $transaction_status,
                'fraud_status' => $fraud_status
            ]);

        // Cari order berdasarkan midtrans_order_id terlebih dahulu
        // Cari order berdasarkan midtrans_order_id terlebih dahulu
        $order = Order::where('midtrans_order_id', $order_id)->first();

        // Jika tidak ditemukan, coba cari berdasarkan order_number untuk kompatibilitas
        if (!$order) {
            $order = Order::where('order_number', $order_id)->first();
        }

        if (!$order) {
            Log::error('Order not found for notification:', ['order_id' => $order_id]);
            return response('Order not found', 404);
        }

        if ($transaction_status == 'capture') {
            if ($fraud_status == 'challenge') {
                // TODO: Set payment status in merchant's database to 'Challenge'
                $order->status = 'challenge';
            } else if ($fraud_status == 'accept') {
                // TODO: Set payment status in merchant's database to 'Success'
                $previousStatus = $order->status; // Simpan status sebelumnya
                $order->status = 'confirmed';

                // Kurangi stok produk jika status berubah dari pending ke confirmed
                if ($previousStatus !== 'confirmed') {
                    foreach ($order->items as $item) {
                        $product = $item->product;

                        // Ambil stok sebelum dikurangi
                        $currentStock = $product->stock;

                        // Pastikan tidak mengurangi lebih dari stok yang tersedia
                        $reduceAmount = min($item->quantity, $currentStock);

                        // Kurangi stok
                        $product->decrement('stock', $reduceAmount);

                        // Refresh model untuk mendapatkan stok terbaru setelah decrement
                        $product->refresh();

                        // Jika stok habis, ubah status produk menjadi 'sold'
                        if ($product->stock <= 0) {
                            $product->update(['status' => 'sold', 'stock' => 0]);
                        } else {
                            // Jika masih ada stok, pastikan status tetap 'available'
                            $product->update(['status' => 'available']);
                        }
                    }
                }

                // Kirim pesan ke pembeli bahwa pembayaran berhasil dan pesanan dikonfirmasi
                // Ambil penjual dari produk pertama (karena semua produk dalam satu order seharusnya dari penjual yang sama)
                $seller = $order->items->first()->product->user;
                Message::create([
                    'sender_id' => $seller->id,
                    'receiver_id' => $order->user_id,
                    'order_id' => $order->id,
                    'message' => "Pembayaran pesanan #{$order->order_number} telah berhasil. Pesanan Anda sedang diproses.",
                    'is_read' => false
                ]);
            }
        } else if ($transaction_status == 'settlement') {
            // TODO: Set payment status in merchant's database to 'Success'
            $previousStatus = $order->status; // Simpan status sebelumnya
            $order->status = 'confirmed';

            // Kurangi stok produk jika status berubah dari pending ke confirmed
            if ($previousStatus !== 'confirmed') {
                foreach ($order->items as $item) {
                    $product = $item->product;

                    // Ambil stok sebelum dikurangi
                    $currentStock = $product->stock;

                    // Pastikan tidak mengurangi lebih dari stok yang tersedia
                    $reduceAmount = min($item->quantity, $currentStock);

                    // Kurangi stok
                    $product->decrement('stock', $reduceAmount);

                    // Refresh model untuk mendapatkan stok terbaru setelah decrement
                    $product->refresh();

                    // Jika stok habis, ubah status produk menjadi 'sold'
                    if ($product->stock <= 0) {
                        $product->update(['status' => 'sold', 'stock' => 0]);
                    } else {
                        // Jika masih ada stok, pastikan status tetap 'available'
                        $product->update(['status' => 'available']);
                    }
                }
            }

            // Kirim pesan ke pembeli bahwa pembayaran berhasil dan pesanan dikonfirmasi
            $seller = $order->items->first()->product->user;
            Message::create([
                'sender_id' => $seller->id,
                'receiver_id' => $order->user_id,
                'order_id' => $order->id,
                'message' => "Pembayaran pesanan #{$order->order_number} telah berhasil. Pesanan Anda sedang diproses.",
                'is_read' => false
            ]);
        } else if ($transaction_status == 'pending') {
            // TODO: Set payment status in merchant's database to 'Pending'
            $order->status = 'waiting_payment';
        } else if ($transaction_status == 'deny') {
            // TODO: Set payment status in merchant's database to 'Failed'
            $order->status = 'pending';  // Tetap di pending karena pembayaran gagal
        } else if ($transaction_status == 'expire') {
            // TODO: Set payment status in merchant's database to 'Failed'
            $order->status = 'pending';  // Tetap di pending karena waktu pembayaran habis
        } else if ($transaction_status == 'cancel') {
            // TODO: Set payment status in merchant's database to 'Failed'
            $order->status = 'pending';  // Tetap di pending karena pembayaran dibatalkan
        }

        $order->save();

        // Log bahwa status telah diperbarui
        Log::info("Order {$order_id} status updated to: {$order->status}");

        return response('OK: Notification received and processed', 200)
            ->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response('Error processing notification', 500);
        }
    }
}
