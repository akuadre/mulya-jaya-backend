<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Product;
use Illuminate\Http\Request;

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
        $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
        ]);

        Product::create([
            'name' => $request->name,
            'type' => $request->type,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
        ], 201);
    }

    // PUT /products/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'type'        => 'sometimes|required|string|max:100',
            'price'       => 'sometimes|required|integer|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        // $product->update([
        //     'name' => $request->name,
        //     'type' => $request->type,
        //     'price' => $request->price,
        //     'stock' => $request->stock,
        //     'description' => $request->description,
        //     'image_url' => $request->image_url,
        // ]);

        $product->update($request->only([
            'name', 'type', 'price', 'stock', 'description', 'image_url'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
        ], 200);
    }

    // DELETE /products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ], 200);
    }
}
