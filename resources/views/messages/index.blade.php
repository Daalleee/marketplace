@extends('layouts.app')

@section('title', 'Pesan - Marketplace Barang Bekas')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary-blue">
                    @if($orderId)
                        Pesan untuk Order #{{ $orderId }}
                    @else
                        Pesan
                    @endif
                </h2>

                @if($orderId)
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-primary-blue">Lihat Semua Percakapan</a>
                @endif
            </div>

            @if($latestMessages->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($latestMessages as $item)
                                <a href="{{ route('messages.chat', $item['user']->id) }}@if($orderId)?order_id={{ $orderId }}@endif" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $item['user']->name }}</h5>
                                        <small>{{ $item['message']->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1 text-muted">Percakapan terakhir</p>
                                        @if($item['unread_count'] > 0)
                                            <span class="badge bg-danger rounded-pill">{{ $item['unread_count'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h4>
                        @if($orderId)
                            Belum ada pesan untuk order ini
                        @else
                            Belum ada pesan
                        @endif
                    </h4>
                    <p class="text-muted">
                        @if($orderId)
                            Anda belum memiliki percakapan untuk order #{{ $orderId }}
                        @else
                            Anda belum memiliki percakapan dengan pengguna lain
                        @endif
                    </p>

                    @if(!$orderId)
                        <a href="{{ route('marketplace') }}" class="btn btn-primary-blue">Cari Produk</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection