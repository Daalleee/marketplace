@extends('layouts.app')

@section('title', isset($product) ? 'Edit Produk' : 'Jual Barang Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white">
                    <h4 class="mb-0">
                        {{ isset($product) ? 'Edit Produk' : 'Jual Barang Baru' }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" 
                          action="{{ isset($product) ? route('product.update', $product->id) : route('sell.store') }}" 
                          enctype="multipart/form-data">
                        @csrf
                        @if(isset($product))
                            @method('PUT')
                        @endif
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Kategori section -->
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>

                            <!-- Existing categories -->
                            <div class="mb-2">
                                <label class="form-label">Pilih Kategori yang Ada:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                                            id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="selectedCategoryText">Pilih Kategori</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="categoryDropdown">
                                        <li><a class="dropdown-item" href="#" data-value="">Pilih Kategori</a></li>
                                        @foreach($categories as $category)
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               data-value="{{ $category->id }}"
                                               data-name="{{ $category->name }}">
                                                {{ $category->name }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <!-- Hidden input untuk menyimpan nilai kategori -->
                                    <input type="hidden" id="category_id" name="category_id"
                                           value="{{ old('category_id', $product->category_id ?? '') }}">
                                </div>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-center my-2">
                                <span class="text-muted">atau</span>
                            </div>

                            <!-- New category input -->
                            <div class="mb-2">
                                <label for="new_category" class="form-label">Buat Kategori Baru:</label>
                                <input type="text" class="form-control @error('new_category') is-invalid @enderror"
                                       id="new_category" name="new_category"
                                       value="{{ old('new_category') }}"
                                       placeholder="Ketik kategori baru...">
                                @error('new_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-text">
                                <small>Anda bisa memilih dari kategori yang tersedia atau membuat kategori baru.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                           id="price" name="price"
                                           value="{{ old('price', $product->price ?? '') }}"
                                           min="0" step="100" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stok Barang <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                           id="stock" name="stock"
                                           value="{{ old('stock', $product->stock ?? '1') }}"
                                           min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Lokasi section -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location"
                                   value="{{ old('location', $product->location ?? '') }}"
                                   placeholder="Masukkan lokasi barang dijual..." required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="condition" class="form-label">Kondisi Barang <span class="text-danger">*</span></label>
                            <select class="form-select @error('condition') is-invalid @enderror"
                                    id="condition" name="condition" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baru" {{ (old('condition', $product->condition ?? '') == 'baru') ? 'selected' : '' }}>
                                    Baru
                                </option>
                                <option value="bekas_baik" {{ (old('condition', $product->condition ?? '') == 'bekas_baik') ? 'selected' : '' }}>
                                    Bekas Baik
                                </option>
                                <option value="bekas_sedang" {{ (old('condition', $product->condition ?? '') == 'bekas_sedang') ? 'selected' : '' }}>
                                    Bekas Sedang
                                </option>
                                <option value="bekas_kurang" {{ (old('condition', $product->condition ?? '') == 'bekas_kurang') ? 'selected' : '' }}>
                                    Bekas Kurang
                                </option>
                            </select>
                            @error('condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Barang <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Barang</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            @if(isset($product) && $product->image)
                                <div class="mt-2">
                                    <p class="text-muted">Gambar saat ini:</p>
                                    <img src="{{ Storage::url($product->image) }}" 
                                         class="img-thumbnail" width="200" alt="Gambar Produk">
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('marketplace') }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary-blue">
                                {{ isset($product) ? 'Update Produk' : 'Jual Barang' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryHiddenInput = document.getElementById('category_id');
    const newCategoryInput = document.getElementById('new_category');
    const selectedCategoryText = document.getElementById('selectedCategoryText');
    const categoryDropdownItems = document.querySelectorAll('#categoryDropdown + ul.dropdown-menu a');

    // Set the initial selected text based on the hidden input value
    if (categoryHiddenInput.value) {
        const selectedItem = Array.from(categoryDropdownItems).find(item =>
            item.getAttribute('data-value') === categoryHiddenInput.value
        );
        if (selectedItem) {
            selectedCategoryText.textContent = selectedItem.getAttribute('data-name');
        }
    }

    // Function to reset error messages when user makes a selection
    function resetCategoryErrors() {
        const categoryError = document.querySelector('.invalid-feedback[style*="display"]');
        if (categoryError) {
            categoryError.style.display = 'none';
        }
    }

    // Handle category dropdown item click
    categoryDropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const value = this.getAttribute('data-value');
            const name = this.getAttribute('data-name');

            categoryHiddenInput.value = value;
            selectedCategoryText.textContent = name || 'Pilih Kategori';

            if (value) {
                newCategoryInput.value = '';
                newCategoryInput.disabled = true;
            } else {
                newCategoryInput.disabled = false;
            }
            resetCategoryErrors();
        });
    });

    // When user types in new category, clear the category selection
    newCategoryInput.addEventListener('input', function() {
        if (this.value.trim()) {
            categoryHiddenInput.value = '';
            selectedCategoryText.textContent = 'Pilih Kategori';
        }
        resetCategoryErrors();
    });
});
</script>

@endsection