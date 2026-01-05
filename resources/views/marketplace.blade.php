@extends('layouts.app')

@section('title', 'Marketplace - Barang Bekas')

@section('content')
    <div class="container">
        <!-- Products Grid -->
        <div class="row mt-3">
            <div class="col-12">
                <h2 class="text-primary-blue mb-4 text-center">Produk Tersedia</h2>

                <div class="row">
                    @forelse($products as $product)
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card card-product h-100">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" class="card-img-top"
                                        alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <img src="https://via.placeholder.com/300x200/000090/FFFFFF?text={{ urlencode($product->name) }}"
                                        class="card-img-top" alt="{{ $product->name }}"
                                        style="height: 200px; object-fit: cover;">
                                @endif

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text fw-bold">Rp
                                        {{ number_format($product->price, 0, ',', '.') }}</p>

                                    @if($product->location)
                                    <div class="mb-2">
                                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $product->location }}</small>
                                    </div>
                                    @else
                                    <div class="mb-2">
                                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Lokasi tidak disebutkan</small>
                                    </div>
                                    @endif

                                    <div class="mt-auto">
                                        @auth
                                            @if (Auth::id() != $product->user_id)
                                                <div class="d-grid gap-2">
                                                    <form method="POST" action="{{ route('cart.add', $product->id) }}"
                                                        class="add-to-cart-form">
                                                        @csrf
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit" class="btn btn-add-to-cart btn-sm w-100 mb-2">
                                                            <i class="fas fa-shopping-cart me-1"></i>Tambah ke Keranjang
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('product.show', $product->id) }}"
                                                        class="btn btn-buy btn-sm w-100">Beli Sekarang</a>
                                                </div>
                                            @else
                                                <a href="{{ route('product.show', $product->id) }}"
                                                    class="btn btn-primary-blue btn-sm w-100">Lihat Detail</a>
                                            @endif
                                        @else
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('login') }}" class="btn btn-add-to-cart btn-sm w-100 mb-2">
                                                    <i class="fas fa-shopping-cart me-1"></i>Tambah ke Keranjang
                                                </a>
                                                <a href="{{ route('login') }}" class="btn btn-buy btn-sm w-100">Beli
                                                    Sekarang</a>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h4>Produk tidak ditemukan</h4>
                                <p class="text-muted">Belum ada produk yang tersedia saat ini</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Alert messages -->
    <div id="alert-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1000;">
        <div id="toast-alert" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toast-message">Action completed successfully!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Tambahkan event listener untuk form submission
        document.addEventListener('DOMContentLoaded', function() {
            const cartForms = document.querySelectorAll('.add-to-cart-form');

            cartForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;

                    // Show loading state
                    submitButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                    submitButton.disabled = true;

                    // Biarkan form tetap submit secara normal untuk menampilkan pesan flash
                    // Tapi enable kembali button setelah submit
                    setTimeout(function() {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }, 1000); // Hanya 1 detik karena ini seharusnya cepat
                });
            });
        });
    </script>
@endsection
