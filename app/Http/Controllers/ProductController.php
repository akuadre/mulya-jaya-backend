<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /products
    public function index()
    {
        return response()->json(Product::all(), 200);
    }

    // GET /products/{id}
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'type' => $product->type,
            'price' => $product->price,
            'stock' => $product->stock,
            'description' => $product->description,
            'image_url' => $product->image_url,
        ], 200);
    }

    // POST /products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
            'total_rating' => 'nullable|integer|min:0',
            'harga' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product berhasil ditambahkan',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'price' => $product->price,
                'stock' => $product->stock,
                'description' => $product->description,
                'image_url' => $product->image_url,
            ],
        ], 201);
    }

    // PUT /products/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
            'total_rating' => 'nullable|integer|min:0',
            'harga' => 'sometimes|required|integer|min:0',
        ]);

        $product = Product::findOrFail($id);
        $product->update($validated);

        return response()->json([
            'message' => 'Product berhasil diupdate',
            'data' => $product,
        ], 200);
    }

    // DELETE /products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product berhasil dihapus',
        ], 200);
    }
}
