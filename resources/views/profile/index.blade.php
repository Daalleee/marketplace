@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Profil Saya</h5>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-5x text-primary-blue mb-3"></i>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    @if($user->phone)
                        <p><i class="fas fa-phone me-2"></i>{{ $user->phone }}</p>
                    @endif
                    
                    @if($user->address)
                        <p><i class="fas fa-map-marker-alt me-2"></i>{{ $user->address }}</p>
                    @endif
                    
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary-blue w-100 mt-3">
                        <i class="fas fa-edit me-2"></i>Edit Profil
                    </a>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user me-2"></i>Profil Saya
                    </a>
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i>Riwayat Transaksi
                    </a>
                    <a href="{{ route('sell.create') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i>Jual Barang Baru
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Produk yang Dijual</h5>
                </div>
                <div class="card-body">
                    @if($products->count() > 0)
                        <div class="row">
                            @foreach($products as $product)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             class="card-img-top" alt="{{ $product->name }}" 
                                             style="height: 120px; object-fit: cover;">
                                    @else
                                        <img src="https://via.placeholder.com/200x120/000090/FFFFFF?text={{ urlencode($product->name) }}" 
                                             class="card-img-top" alt="{{ $product->name }}" 
                                             style="height: 120px; object-fit: cover;">
                                    @endif
                                    
                                    <div class="card-body p-3">
                                        <h6 class="card-title">{{ Str::limit($product->name, 25) }}</h6>
                                        <p class="card-text text-primary-blue fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                        <span class="badge badge-condition {{ $product->condition }} mb-2 d-block">
                                            {{ str_replace('_', ' ', $product->condition) }}
                                        </span>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('product.edit', $product->id) }}" 
                                               class="btn btn-sm btn-outline-primary flex-fill">Edit</a>
                                            <form method="POST" action="{{ route('product.destroy', $product->id) }}" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger flex-fill"
                                                        onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>Belum ada produk</h5>
                            <p class="text-muted">Anda belum menjual produk apapun</p>
                            <a href="{{ route('sell.create') }}" class="btn btn-primary-blue">
                                <i class="fas fa-plus me-2"></i>Jual Barang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary-blue text-white">
                    <h5 class="mb-0">Transaksi Terbaru</h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No. Order</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders->take(5) as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('orders.show', $order->id) }}" 
                                               class="btn btn-sm btn-outline-primary">Lihat</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('orders.index') }}" class="btn btn-link text-primary-blue">Lihat semua transaksi</a>
                    @else
                        <p class="text-muted">Anda belum memiliki transaksi apapun.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection