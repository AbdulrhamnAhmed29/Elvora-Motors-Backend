<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductsController extends Controller
{
    // عرض كل العربيات
    public function index()
    {
        return response()->json(Product::all());
    }

    // إضافة عربية جديدة
    public function create(Request $request)
    {
      $request->validate([
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'price' => 'required|numeric|min:0',
    'is_available' => 'required|in:yes,no',
    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
]);


        $product = new Product();
        $product->title        = $request->title;
        $product->description  = $request->description;
        $product->price        = $request->price;
        $product->is_available = $request->is_available;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $product->image = url('/images/' . $filename);
        }

        $product->save();
        return response()->json(['message' => 'Product Created Successfully', 'product' => $product], 201);
    }

    // عرض عربية واحدة
    public function getbyId($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Not Found'], 404);
        return response()->json($product);
    }

    // تعديل عربية
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->title        = $request->title ?? $product->title;
        $product->description  = $request->description ?? $product->description;
        $product->price        = $request->price ?? $product->price;
        $product->is_available = $request->has('is_available') ? $request->is_available : $product->is_available;

        if ($request->hasFile('image')) {
            $oldFilename = basename($product->image);
            $oldPath = public_path('images/' . $oldFilename);
            if (File::exists($oldPath)) File::delete($oldPath);

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $product->image = url('/images/' . $filename);
        }

        $product->save();
        return response()->json(['message' => 'Product Updated Successfully', 'product' => $product]);
    }

    // حذف عربية
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json(['message' => 'Product Deleted']);
        }
        return response()->json(['message' => 'Product Not Found'], 404);
    }

    // 📊 الإحصائيات
    public function stats()
    {
        return response()->json([
            'total_cars'        => Product::count(),
            'available_cars'    => Product::where('is_available', true)->count(),
            'not_available_cars'=> Product::where('is_available', false)->count(),
        ]);
    }
}
