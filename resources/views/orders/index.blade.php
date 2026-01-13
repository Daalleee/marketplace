@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="container">
    <h2 class="text-primary-blue mb-4">Riwayat Transaksi</h2>
    
    @if($orders->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No. Order</th>
                                <th>Tanggal</th>
                                <th>Barang Dibeli</th>
                                <th>Total</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @forelse($order->items as $item)
                                        <div>{{ $item->product->name ?? 'Produk tidak ditemukan' }}</div>
                                        @if(!$loop->last)<hr class="my-1">@endif
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge
                                        @if(in_array($order->status, ['confirmed', 'shipped', 'delivered'])) bg-success-custom
                                        @elseif($order->status == 'waiting_payment') bg-warning
                                        @else bg-danger
                                        @endif">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
            <h4>Belum ada transaksi</h4>
            <p class="text-muted">Anda belum memiliki riwayat transaksi apapun</p>
            <a href="{{ route('marketplace') }}" class="btn btn-primary-blue">Belanja Sekarang</a>
        </div>
    @endif
</div>
@endsection