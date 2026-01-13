@extends('layouts.app')

@section('title', 'Pembayaran - ' . $order->order_number)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h4 class="mb-0">Pembayaran Pesanan #{{ $order->order_number }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Rincian Pesanan</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total:</th>
                                    <th>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Langkah Pembayaran</h5>
                        <ol>
                            <li>Klik tombol "Bayar Sekarang" di bawah ini</li>
                            <li>Ikuti instruksi pembayaran di halaman Midtrans</li>
                            <li>Setelah pembayaran selesai, status pesanan akan otomatis terupdate</li>
                            <li>Penjual akan menghubungi Anda untuk konfirmasi pengiriman</li>
                        </ol>
                    </div>

                    <div class="d-grid">
                        <button id="pay-button" class="btn btn-primary-blue btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Bayar Sekarang - Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans Payment Script -->
<script type="text/javascript">
    // Set sanitize for Midtrans
    var snap_token = '{{ $snapToken }}';
    
    // Create a Snap instance
    var btn = document.getElementById('pay-button');
    btn.addEventListener('click', function() {
        // Show Midtrans Snap popup
        snap.pay(snap_token, {
            onSuccess: function(result) {
                // Payment success
                console.log(result);
                alert("Pembayaran Berhasil!");
                
                // Redirect to order details page
                window.location.href = "{{ route('orders.show', $order->id) }}";
            },
            onPending: function(result) {
                // Payment pending
                console.log(result);
                alert("Menunggu Pembayaran!");
                
                // Redirect to order details page
                window.location.href = "{{ route('orders.show', $order->id) }}";
            },
            onError: function(result) {
                // Payment error
                console.log(result);
                alert("Pembayaran Gagal!");
                
                // Redirect to order details page
                window.location.href = "{{ route('orders.show', $order->id) }}";
            }
        });
    });
</script>

<!-- Include Midtrans Snap Script -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endsection