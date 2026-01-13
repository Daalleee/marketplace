@extends('layouts.app')

@section('title', 'Detail Transaksi - ' . $order->order_number)

@section('content')
<div class="container">
    <h2 class="text-primary-blue mb-4">Detail Transaksi</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>No. Order:</strong> {{ $order->order_number }}</p>
                            <p><strong>Tanggal:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                            <p><strong>Status Pembayaran:</strong>
                                <span class="badge
                                    @if(in_array($order->status, ['confirmed', 'shipped', 'delivered'])) bg-success-custom
                                    @elseif($order->status == 'waiting_payment') bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ $order->payment_status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Pembayaran:</strong></p>
                            <h4 class="text-primary-blue">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Item yang Dipesan</h5>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-2">
                            @if($item->product->image)
                                <img src="{{ Storage::url($item->product->image) }}" 
                                     class="img-fluid rounded" alt="{{ $item->product->name }}" width="60">
                            @else
                                <img src="https://via.placeholder.com/60x60/000090/FFFFFF?text={{ urlencode($item->product->name) }}" 
                                     class="img-fluid rounded" alt="{{ $item->product->name }}" width="60">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>{{ $item->product->name }}</h6>
                            <small class="text-muted">Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }}</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong class="text-primary-blue">
                                Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Ringkasan Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkos Kirim:</span>
                        <span class="text-success">Gratis</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total:</strong>
                        <strong class="text-primary-blue">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                    
                    @if($order->status !== 'cancelled')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            @if($order->payment_status == 'Lunas')
                                Pembayaran telah lunas. Pesanan sedang diproses/pengiriman.
                            @elseif($order->payment_status == 'Menunggu Pembayaran')
                                <p>Pesanan menunggu pembayaran. Silakan selesaikan pembayaran untuk melanjutkan.</p>
                                <a href="{{ route('orders.payment', $order->id) }}" class="btn btn-primary-blue">
                                    <i class="fas fa-credit-card me-2"></i>Bayar Sekarang
                                </a>
                            @else
                                Pembayaran belum lunas.
                            @endif
                        </div>
                    @endif

                    <!-- Tombol komunikasi dengan penjual -->
                    @php
                        $seller = $order->items->first() ? $order->items->first()->product->user : null;
                        $isBuyer = Auth::id() == $order->user_id;  // Jika user_id order sama dengan user yang login
                        $isSeller = $seller && $seller->id == Auth::id();  // Jika user yang login adalah penjual produk
                    @endphp
                    @if($seller && $seller->id !== Auth::id())
                        <div class="card border-primary-blue mt-4">
                            <div class="card-body text-center">
                                <h5 class="card-title text-primary-blue">
                                    <i class="fas fa-comments me-2"></i>
                                    @if($isBuyer)
                                        Komunikasi dengan Penjual
                                    @elseif($isSeller)
                                        Komunikasi dengan Pembeli
                                    @endif
                                </h5>
                                <p class="card-text text-muted mb-3">
                                    @if($isBuyer)
                                        Ingin bertanya tentang pesanan Anda atau mengatur pengiriman? Klik tombol di bawah untuk menghubungi penjual langsung.
                                    @elseif($isSeller)
                                        Ingin menginformasikan tentang pengiriman atau menjawab pertanyaan pembeli? Klik tombol di bawah untuk menghubungi pembeli langsung.
                                    @endif
                                </p>
                                <a href="{{ route('messages.chat', $isBuyer ? $seller->id : $order->user_id) }}?order_id={{ $order->id }}" class="btn btn-primary-blue btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    @if($isBuyer)
                                        Kirim Pesan ke Penjual
                                    @elseif($isSeller)
                                        Kirim Pesan ke Pembeli
                                    @endif
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection