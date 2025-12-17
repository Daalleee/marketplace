<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Cache kategori selama 60 menit karena jarang berubah
        $categories = Cache::remember('categories', 60 * 60, function () {
            return Category::all();
        });

        // Gunakan caching untuk produk dengan mempertimbangkan parameter pencarian
        $cacheKey = 'products_page_' . $request->page . '_' . md5(serialize($request->all()));

        $products = Cache::remember($cacheKey, 300, function () use ($request) {
            return Product::select(['id', 'user_id', 'category_id', 'name', 'price', 'condition', 'image', 'status', 'stock', 'created_at', 'location'])
                ->with(['user:id,name', 'category:id,name'])
                ->where('status', 'available')
                ->where('stock', '>', 0)  // Hanya tampilkan produk yang stoknya lebih dari 0
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->when($request->category, function ($query, $category) {
                    $query->where('category_id', $category);
                })
                ->when($request->min_price, function ($query, $min_price) {
                    $query->where('price', '>=', $min_price);
                })
                ->when($request->max_price, function ($query, $max_price) {
                    $query->where('price', '<=', $max_price);
                })
                ->paginate(12);
        });

        return view('marketplace', compact('products', 'categories'));
    }

    public function show($id)
    {
        $cacheKey = "product_detail_{$id}";

        $product = Cache::remember($cacheKey, 300, function () use ($id) {
            return Product::with(['user', 'category', 'reviews.user'])->findOrFail($id);
        });

        $averageRating = $product->getAverageRatingAttribute();
        $reviewCount = $product->getReviewCountAttribute();

        return view('product-detail', compact('product', 'averageRating', 'reviewCount'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('sell-form', compact('categories'));
    }

    public function store(Request $request)
    {
        // Custom validation to ensure either category_id or new_category is provided
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:baru,bekas_baik,bekas_sedang,bekas_kurang',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validate that either category_id or new_category is provided
        if (!$request->filled('category_id') && !$request->filled('new_category')) {
            return redirect()->back()->withErrors(['category' => 'Silakan pilih kategori atau buat kategori baru.'])->withInput();
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Handle category - either use existing or create new
        if ($request->filled('category_id')) {
            // Use existing category
            $data['category_id'] = $request->category_id;
        } elseif ($request->filled('new_category')) {
            // Create new category
            $category = Category::firstOrCreate(
                ['name' => strtolower(trim($request->new_category))],
                ['name' => trim($request->new_category), 'description' => 'Kategori baru']
            );
            $data['category_id'] = $category->id;
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        Product::create($data);

        return redirect()->route('marketplace')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        // Cek apakah produk milik user yang sedang login
        if ($product->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }
        
        $categories = Category::all();
        return view('sell-form', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Cek apakah produk milik user yang sedang login
        if ($product->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengupdate produk ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:baru,bekas_baik,bekas_sedang,bekas_kurang',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validate that either category_id or new_category is provided
        if (!$request->filled('category_id') && !$request->filled('new_category')) {
            return redirect()->back()->withErrors(['category' => 'Silakan pilih kategori atau buat kategori baru.'])->withInput();
        }

        $data = $request->all();

        // Handle category - either use existing or create new
        if ($request->filled('category_id')) {
            // Use existing category
            $data['category_id'] = $request->category_id;
        } elseif ($request->filled('new_category')) {
            // Create new category
            $category = Category::firstOrCreate(
                ['name' => strtolower(trim($request->new_category))],
                ['name' => trim($request->new_category), 'description' => 'Kategori baru']
            );
            $data['category_id'] = $category->id;
        }

        if ($request->hasFile('image')) {
            // Hajar gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        } else {
            unset($data['image']); // Jangan update kolom image jika tidak ada file baru
        }

        $product->update($data);

        return redirect()->route('profile.index')->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Cek apakah produk milik user yang sedang login
        if ($product->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus produk ini.');
        }

        // Hapus gambar jika ada
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('profile.index')->with('success', 'Produk berhasil dihapus!');
    }

    public function buyNow(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        // Pastikan produk tersedia
        if ($product->status !== 'available') {
            return redirect()->back()->with('error', 'Produk tidak tersedia untuk pembelian.');
        }

        // Cek stok produk
        $quantity = $request->input('quantity', 1);
        if ($product->stock < $quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi. Tersisa: ' . $product->stock . ' unit.');
        }

        // Dapatkan atau buat keranjang untuk user
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['user_id' => Auth::id()]
        );

        // Cari item keranjang atau buat baru
        $cartItem = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $productId,
        ]);

        // Cek total jumlah setelah penambahan
        $newQuantity = $cartItem->exists ? $cartItem->quantity + $quantity : $quantity;
        if ($product->stock < $newQuantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi. Tersisa: ' . $product->stock . ' unit. Saat ini di keranjang: ' . ($cartItem->exists ? $cartItem->quantity : 0) . ' unit.');
        }

        if (!$cartItem->exists) {
            $cartItem->quantity = $quantity;
            $cartItem->save();
        } else {
            $cartItem->increment('quantity', $quantity);
        }

        // Redirect ke checkout
        return redirect()->route('checkout.index');
    }

    // Method untuk submit review produk
    public function submitReview(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $product = Product::findOrFail($productId);

        // Cek apakah user sudah pernah mereview produk ini
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($existingReview) {
            // Jika sudah pernah mereview, update review yang sudah ada
            $existingReview->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return redirect()->back()->with('success', 'Review berhasil diperbarui!');
        } else {
            // Jika belum pernah mereview, buat review baru
            Review::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return redirect()->back()->with('success', 'Review berhasil ditambahkan!');
        }
    }
}