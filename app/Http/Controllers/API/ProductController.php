<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\AuditLogService;

class ProductController extends Controller
{
    // GET /products
    public function index()
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada produk tersedia',
            ], 404);
        }

        // mapping data biar 'type' jadi ucfirst
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'type' => ucfirst($product->type),
                'price' => $product->price,
                'stock' => $product->stock,
                'description' => $product->description,
                'image_url' => $product->image_url,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil',
            'data' => $data,
        ], 200);
    }

    // GET /products/{id}
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditemukan',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => ucfirst($product->type),
                'price' => $product->price,
                'stock' => $product->stock,
                'description' => $product->description,
                'image_url' => $product->image_url,
            ],
        ], 200);
    }

    // POST /products
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'        => 'required|string|max:255',
                'type'        => 'required|string|max:100',
                'price'       => 'required|integer|min:0',
                'stock'       => 'required|integer|min:0',
                'description' => 'nullable|string',
                'photo'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $imagePath = null;
            if ($request->hasFile('photo')) {
                // Simpan gambar ke folder public/images/products
                $imagePath = $request->file('photo')->hashName();
                $request->file('photo')->move(public_path('images/products'), $imagePath);
            }

            $product = Product::create([
                'name'        => $request->name,
                'type'        => $request->type,
                'price'       => $request->price,
                'stock'       => $request->stock,
                'description' => $request->description,
                'image_url'   => $imagePath,
            ]);

            // ✅ LOG AUDIT - Produk dibuat
            AuditLogService::logProductAction('create', $product->id, $product->name);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.',
                'data' => $product,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat produk: ' . $e->getMessage()
            ], 500);
        }
    }

    // PUT /products/{id}
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name'        => 'required|string|max:255',
                'type'        => 'required|string|max:100',
                'price'       => 'required|integer|min:0',
                'stock'       => 'required|integer|min:0',
                'description' => 'nullable|string',
                'photo'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Simpan data lama untuk log
            $oldData = $product->toArray();

            $imagePath = $product->image_url;

            if ($request->hasFile('photo')) {
                // Hapus gambar lama jika ada
                if ($product->image_url && file_exists(public_path('images/products/' . $product->image_url))) {
                    unlink(public_path('images/products/' . $product->image_url));
                }

                // Simpan gambar baru
                $imagePath = $request->file('photo')->hashName();
                $request->file('photo')->move(public_path('images/products'), $imagePath);
            }

            $product->update([
                'name'        => $request->name,
                'type'        => $request->type,
                'price'       => $request->price,
                'stock'       => $request->stock,
                'description' => $request->description,
                'image_url'   => $imagePath,
            ]);

            // ✅ LOG AUDIT - Produk diupdate
            AuditLogService::logProductAction(
                'update', 
                $product->id, 
                $product->name,
                $oldData,
                $product->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui.',
                'data' => $product,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE /products/{id}
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            $productName = $product->name;
            $productId = $product->id;

            if ($product->image_url && file_exists(public_path('images/products/' . $product->image_url))) {
                unlink(public_path('images/products/' . $product->image_url));
            }

            $product->delete();

            // ✅ LOG AUDIT - Produk dihapus
            AuditLogService::logProductAction('delete', $productId, $productName);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage()
            ], 500);
        }
    }
}