<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function dashboard()
    {
        return view('supplier.dashboard');
    }

    public function showAddProductForm()
    {
        return view('supplier.add_product');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wholesale_rate' => 'required|numeric|min:0',
            'retail_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        Product::create([
            'supplier_id' => Auth::id(),
            'name' => $request->name,
            'wholesale_rate' => $request->wholesale_rate,
            'retail_rate' => $request->retail_rate,
            'stock' => $request->stock,
            'images' => json_encode($images),
            'status' => 'pending',
        ]);

        return redirect()->route('supplier.my_products')->with('success', 'Product added successfully.');
    }

    public function myProducts()
    {
        $products = Product::where('supplier_id', Auth::id())->get();
        return view('supplier.my_products', compact('products'));
    }

    public function showEditProductForm($id)
    {
        $product = Product::where('id', $id)->where('supplier_id', Auth::id())->firstOrFail();
        if ($product->status !== 'pending') {
            return redirect()->route('supplier.my_products')->with('error', 'Cannot edit approved/rejected product.');
        }
        return view('supplier.edit_product', compact('product'));
    }

    public function editProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->where('supplier_id', Auth::id())->firstOrFail();
        if ($product->status !== 'pending') {
            return redirect()->route('supplier.my_products')->with('error', 'Cannot edit approved/rejected product.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'wholesale_rate' => 'required|numeric|min:0',
            'retail_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $images = json_decode($product->images, true) ?? [];
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        $product->update([
            'name' => $request->name,
            'wholesale_rate' => $request->wholesale_rate,
            'retail_rate' => $request->retail_rate,
            'stock' => $request->stock,
            'images' => json_encode($images),
        ]);

        return redirect()->route('supplier.my_products')->with('success', 'Product updated successfully.');
    }

    public function payments()
    {
        $payments = Payment::where('supplier_id', Auth::id())->get();
        return view('supplier.payments', compact('payments'));
    }
}