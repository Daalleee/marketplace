@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container">
    <h2 class="text-primary-blue mb-4">Keranjang Belanja</h2>
    
    @if($cartItems->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4>Keranjang Anda Kosong</h4>
            <p class="text-muted">Tambahkan beberapa produk untuk melanjutkan</p>
            <a href="{{ route('marketplace') }}" class="btn btn-primary-blue">Lihat Produk</a>
        </div>
    @else
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        @foreach($cartItems as $item)
                        <div class="row mb-4 pb-4 border-bottom">
                            <div class="col-md-3">
                                @if($item->product->image)
                                    <img src="{{ Storage::url($item->product->image) }}" 
                                         class="img-fluid rounded" alt="{{ $item->product->name }}">
                                @else
                                    <img src="https://via.placeholder.com/150x150/000090/FFFFFF?text={{ urlencode($item->product->name) }}" 
                                         class="img-fluid rounded" alt="{{ $item->product->name }}">
                                @endif
                            </div>
                            
                            <div class="col-md-6">
                                <h5>{{ $item->product->name }}</h5>
                                <p class="text-primary-blue fw-bold">Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                <p class="text-muted">{{ $item->product->description }}</p>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <div class="mb-2">
                                        <label for="quantity_{{ $item->id }}">Jumlah:</label>
                                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group">
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="quantity" value="{{ $item->quantity }}" 
                                                       min="1" max="999" id="quantity_{{ $item->id }}">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <strong class="text-primary-blue">
                                            Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                                        </strong>
                                    </div>
                                    
                                    <form method="POST" action="{{ route('cart.remove', $item->id) }}" class="mt-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary-blue text-white">
                        <h5 class="mb-0">Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Item:</span>
                            <strong>{{ $cartItems->sum('quantity') }}</strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span>Total Harga:</span>
                            <strong class="text-primary-blue">
                                Rp {{ number_format($cartItems->sum(function($item) { return $item->product->price * $item->quantity; }), 0, ',', '.') }}
                            </strong>
                        </div>
                        
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary-blue w-100 py-3">
                            <i class="fas fa-shopping-bag me-2"></i>Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection