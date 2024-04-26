<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();

            $products = Product::all();
            $products = Product::paginate(10);
            $products->each(function ($product) {
                $product->image = $product->image ? Storage::url('/' . $product->image) : null;
            });            

            return view('dashboard.dashboard', compact('categories', 'products'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to retrieve products.');
        }
    }

    public function create()
    {
        try {
            $categoryNames = Category::pluck('name')->toArray();
            $product = new Product();
            return view('dashboard.create', compact('categoryNames', 'product'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to retrieve categories.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_produk' => 'required|unique:products',
                'kategori_produk' => [
                    'required',
                    Rule::in(Category::pluck('name')->toArray()),
                ],
                'harga_beli' => 'required|numeric',
                'stok_produk' => 'required|integer',
                'image' => 'required|image|mimes:jpeg,png|max:100',
            ]);

            $hargaBeli = $validatedData['harga_beli'];
            $hargaJual = $hargaBeli * 1.3;

            $categoryId = Category::where('name', $validatedData['kategori_produk'])->value('id');

            $imagePath = $request->file('image')->store('product_images', 'public');

            DB::insert('INSERT INTO products (
                    nama_produk, kategori_produk, harga_beli, harga_jual, stok_produk, image, 
                    created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 
                [
                    $validatedData['nama_produk'],
                    $categoryId,
                    $hargaBeli,
                    $hargaJual,
                    $validatedData['stok_produk'],
                    $imagePath,
                    now(),
                    now(),
                ]
            );

            return redirect()->route('dashboard')->with('success', 'Product added successfully.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to add product.');
        }
    }

    public function edit($id)
    {
        try {
            $product = DB::table('products')->find($id);

            // Check if the product exists
            if (!$product) {
                return redirect()->route('dashboard')->with('error', 'Product not found.');
            }

            $categoryNames = Category::pluck('name')->toArray();

            return view('dashboard.edit', compact('product', 'categoryNames'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to retrieve product data.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nama_produk' => 'required|unique:products,nama_produk,'.$id,
                'kategori_produk' => [
                    'required',
                    Rule::in(Category::pluck('name')->toArray()),
                ],
                'harga_beli' => 'required|numeric',
                'stok_produk' => 'required|integer',
                'image' => 'nullable|image|mimes:jpeg,png|max:100',
            ]);

            $hargaBeli = $validatedData['harga_beli'];
            $hargaJual = $hargaBeli * 1.3;

            $categoryId = Category::where('name', $validatedData['kategori_produk'])->value('id');

            $product = Product::findOrFail($id);

            if ($request->hasFile('image')) {
                // Delete old image if exists
                Storage::disk('public')->delete($product->image);

                // Upload new image
                $imagePath = $request->file('image')->store('product_images', 'public');
                $product->image = $imagePath;
            }

            // Update product details
            $product->nama_produk = $validatedData['nama_produk'];
            $product->kategori_produk = $categoryId;
            $product->harga_beli = $hargaBeli;
            $product->harga_jual = $hargaJual;
            $product->stok_produk = $validatedData['stok_produk'];
            $product->updated_at = now();
            $product->save();

            return redirect()->route('dashboard')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to update product.');
        }
    }


    public function destroy($id)
    {
        try {
            $product = DB::table('products')->find($id);

            if (!$product) {
                return redirect()->route('dashboard')->with('error', 'Product not found.');
            }

            DB::table('products')->where('id', $id)->delete();

            return redirect()->route('dashboard')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to delete product.');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = Product::query()->with('category');

            if ($request->has('category_id')) {
                $query->where('kategori_produk', $request->category_id);
            }

            if ($request->has('nama_produk')) {
                $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
            }
    
            $products = $query->get();

            $products = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'kategori_produk' => $product->category ? $product->category->name : null,
                    'harga_beli' => $product->harga_beli,
                    'harga_jual' => $product->harga_jual,
                    'stok_produk' => $product->stok_produk,
                    'image' => $product->image ? Storage::url('/' . $product->image) : null,
                ];
            });

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch products.'], 500);
        }
    }


    public function export()
    {
        try {
            $title = 'Data Produk';
            return Excel::download(new ProductsExport($title), 'products.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to export products.');
        }
    }
}