@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <h2 class="text-primary-blue mb-4">Checkout</h2>
    
    @if($cartItems->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4>Keranjang Anda Kosong</h4>
            <p class="text-muted">Tambahkan beberapa produk untuk melanjutkan ke checkout</p>
            <a href="{{ route('marketplace') }}" class="btn btn-primary-blue">Lihat Produk</a>
        </div>
    @else
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary-blue text-white">
                        <h5 class="mb-0">Alamat Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('checkout.process') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="{{ old('name', auth()->user()->name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', auth()->user()->address ?? '') }}</textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary-blue w-100 py-3">
                                <i class="fas fa-check-circle me-2"></i>Proses Checkout
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary-blue text-white">
                        <h5 class="mb-0">Detail Pesanan</h5>
                    </div>
                    <div class="card-body">
                        @foreach($cartItems as $item)
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
                                <small class="text-muted">Rp {{ number_format($item->product->price, 0, ',', '.') }} x {{ $item->quantity }}</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <strong class="text-primary-blue">
                                    Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
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
                        <h5 class="mb-0">Ringkasan Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ongkos Kirim:</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total:</strong>
                            <strong class="text-primary-blue">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Pembayaran akan diproses setelah konfirmasi pesanan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection