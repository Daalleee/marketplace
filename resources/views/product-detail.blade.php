@extends('layouts.app')

@section('title', $product->name . ' - Barang Bekas')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}" 
                     class="img-fluid rounded shadow" alt="{{ $product->name }}">
            @else
                <img src="https://via.placeholder.com/500x400/000090/FFFFFF?text={{ urlencode($product->name) }}" 
                     class="img-fluid rounded shadow" alt="{{ $product->name }}">
            @endif
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title">{{ $product->name }}</h1>
                    
                    <div class="mb-3">
                        <label class="form-label">Harga:</label>
                        <span class="fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kondisi Barang:</label>
                        @switch($product->condition)
                            @case('baru')
                                <span class="text-primary fw-bold">Baru</span>
                                @break
                            @case('bekas_baik')
                                <span class="text-success fw-bold">Bekas Baik</span>
                                @break
                            @case('bekas_sedang')
                                <span class="text-warning fw-bold">Bekas Sedang</span>
                                @break
                            @case('bekas_kurang')
                                <span class="text-danger fw-bold">Bekas Kurang</span>
                                @break
                            @default
                                <span class="fw-bold">{{ str_replace('_', ' ', $product->condition) }}</span>
                        @endswitch
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi:</label>
                        @if($product->location)
                            <span class="text-muted">{{ $product->location }}</span>
                        @else
                            <span class="text-muted">Tidak disebutkan</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dijual oleh:</label>
                        <strong>{{ $product->user->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori:</label>
                        <span class="text-muted">{{ $product->category->name }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi:</label>
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stok Tersedia:</label>
                        @if($product->stock > 0)
                            <span class="text-success fw-bold">{{ $product->stock }} unit</span>
                        @else
                            <span class="text-danger fw-bold">Habis</span>
                        @endif
                    </div>

                    @auth
                        @if(Auth::id() != $product->user_id)
                        <div class="mb-3">
                            <label class="form-label">Kontak Penjual:</label>
                            <a href="{{ route('messages.chat', $product->user_id) }}" class="btn btn-primary-blue w-100">
                                <i class="fas fa-envelope me-2"></i>Kirim Pesan
                            </a>
                        </div>
                        @endif
                    @endauth

                    <!-- Bagian Review Produk -->
                    <div class="mt-4 border-top pt-4">
                        <h4>Ulasan Produk ({{ $reviewCount }})</h4>

                        <!-- Tampilkan rating rata-rata -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 me-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $averageRating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </h5>
                                <span class="fw-bold">{{ number_format($averageRating, 1) }}</span>
                                <span class="text-muted ms-2">({{ $reviewCount }} ulasan)</span>
                            </div>
                        </div>

                        <!-- Daftar Review -->
                        @if($product->reviews->count() > 0)
                            <div class="reviews-list">
                                @foreach($product->reviews->sortByDesc('created_at') as $review)
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between">
                                            <h6>{{ $review->user->name }}</h6>
                                            <div>
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                        @if($review->comment)
                                            <p class="mb-1">{{ $review->comment }}</p>
                                        @endif
                                        <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Belum ada ulasan untuk produk ini.</p>
                        @endif

                        <!-- Form Review untuk Pengguna yang Sudah Login -->
                        @auth
                            @if(Auth::id() != $product->user_id)
                                <div class="mt-4">
                                    <h5>Tulis Ulasan Anda</h5>
                                    <form method="POST" action="{{ route('product.review', $product->id) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Rating:</label>
                                            <div class="rating-input">
                                                <input type="radio" class="btn-check" name="rating" id="star1" value="1" autocomplete="off" required>
                                                <label class="btn btn-outline-warning" for="star1"><i class="fas fa-star"></i></label>

                                                <input type="radio" class="btn-check" name="rating" id="star2" value="2" autocomplete="off" required>
                                                <label class="btn btn-outline-warning" for="star2"><i class="fas fa-star"></i></label>

                                                <input type="radio" class="btn-check" name="rating" id="star3" value="3" autocomplete="off" required>
                                                <label class="btn btn-outline-warning" for="star3"><i class="fas fa-star"></i></label>

                                                <input type="radio" class="btn-check" name="rating" id="star4" value="4" autocomplete="off" required>
                                                <label class="btn btn-outline-warning" for="star4"><i class="fas fa-star"></i></label>

                                                <input type="radio" class="btn-check" name="rating" id="star5" value="5" autocomplete="off" required>
                                                <label class="btn btn-outline-warning" for="star5"><i class="fas fa-star"></i></label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Komentar:</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Bagikan pengalaman Anda tentang produk ini..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary-blue">Kirim Ulasan</button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="mt-4">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login untuk memberikan ulasan
                                </a>
                            </div>
                        @endauth
                    </div>

                    <div class="d-grid gap-2">
                        @auth
                            @if(Auth::id() != $product->user_id)
                                @if($product->stock > 0)
                                    <form method="POST" action="{{ route('cart.add', $product->id) }}" class="add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-add-to-cart btn-lg w-100 mb-2">
                                            <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('product.buy-now', $product->id) }}" class="buy-now-form" id="buyNowForm">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-buy btn-lg w-100">
                                            <i class="fas fa-bolt me-2"></i>Beli Sekarang
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary btn-lg w-100" disabled>
                                        <i class="fas fa-times-circle me-2"></i>Stok Habis
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-primary-blue btn-lg w-100">
                                    <i class="fas fa-edit me-2"></i>Edit Produk
                                </a>
                            @endif
                        @else
                            @if($product->stock > 0)
                                <div class="d-grid gap-2">
                                    <a href="{{ route('login') }}" class="btn btn-add-to-cart btn-lg w-100 mb-2">
                                        <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                    </a>
                                    <a href="{{ route('login') }}" class="btn btn-buy btn-lg w-100">
                                        <i class="fas fa-bolt me-2"></i>Beli Sekarang
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-envelope me-2"></i>Kirim Pesan ke Penjual
                                    </a>
                                </div>
                            @else
                                <button class="btn btn-secondary btn-lg w-100" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Stok Habis
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <div class="row mt-5">
        <div class="col-12">
            <h3>Produk Sejenis</h3>
            <div class="row">
                @php
                    $relatedProducts = \App\Models\Product::where('category_id', $product->category_id)
                        ->where('id', '!=', $product->id)
                        ->where('status', 'available')
                        ->take(4)
                        ->get();
                @endphp
                
                @forelse($relatedProducts as $related)
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card card-product h-100">
                            @if($related->image)
                                <img src="{{ Storage::url($related->image) }}" 
                                     class="card-img-top" alt="{{ $related->name }}" 
                                     style="height: 150px; object-fit: cover;">
                            @else
                                <img src="https://via.placeholder.com/300x150/000090/FFFFFF?text={{ urlencode($related->name) }}" 
                                     class="card-img-top" alt="{{ $related->name }}" 
                                     style="height: 150px; object-fit: cover;">
                            @endif
                            
                            <div class="card-body">
                                <h5 class="card-title">{{ Str::limit($related->name, 25) }}</h5>
                                <p class="card-text text-primary-blue fw-bold">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                                <a href="{{ route('product.show', $related->id) }}" 
                                   class="btn btn-buy btn-sm w-100">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted">Tidak ada produk sejenis</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    // Tambahkan event listener untuk form submission
    document.addEventListener('DOMContentLoaded', function() {
        const cartForms = document.querySelectorAll('.add-to-cart-form, .buy-now-form');

        cartForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;

                // Show loading state
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
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