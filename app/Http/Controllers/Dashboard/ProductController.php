<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $products = Product::when($request->search, function ($q) use ($request) {
            return $q->where('name', 'like', '%' . $request->search . '%');
        })->when($request->category_id, function ($q) use ($request) {
            return $q->where('category_id', $request->category_id);
        })->get();

        return view('dashboard.products.index', compact('categories', 'products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('dashboard.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // return $request;
        $rules = [
            'category_id'    => 'required',
            'name'           => 'required|string|max:255|unique:products,name',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];

        $messages = [
            'category_id.required'    => 'حقل القسم مطلوب.',
            'name.required'           => 'حقل الاسم مطلوب.',
            'name.string'             => 'حقل الاسم يجب أن يكون نصًا.',
            'name.max'                => 'حقل الاسم يجب ألا يزيد عن 255 حرفًا.',
            'name.unique'             => 'اسم المنتج مستخدم من قبل، يرجى اختيار اسم آخر.',
            'purchase_price.required' => 'حقل سعر الشراء مطلوب.',
            'purchase_price.numeric'  => 'سعر الشراء يجب أن يكون رقمًا.',
            'purchase_price.min'      => 'سعر الشراء يجب ألا يقل عن 0.',
            'sale_price.required'     => 'حقل سعر البيع مطلوب.',
            'sale_price.numeric'      => 'سعر البيع يجب أن يكون رقمًا.',
            'sale_price.min'          => 'سعر البيع يجب ألا يقل عن 0.',
            'stock.required'          => 'حقل المخزون مطلوب.',
            'stock.integer'           => 'المخزون يجب أن يكون عددًا صحيحًا.',
            'stock.min'               => 'المخزون يجب ألا يقل عن 0.',
        ];

        $request->validate($rules, $messages);

        $request_data = $request->all();

        if ($request->image) {
            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/product_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();
        }
        // return $request_data;

        Product::create($request_data);

        session()->flash('success', 'تم الإضافة بنجاح');
        return redirect()->route('dashboard.products.index');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('dashboard.products.edit', compact('categories', 'product'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'category_id'    => 'required',
            'name'           => 'required|string|max:255|unique:products,name,' . $product->id,
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
        ];

        $messages = [
            'category_id.required'    => 'حقل القسم مطلوب.',
            'name.required'           => 'حقل الاسم مطلوب.',
            'name.string'             => 'حقل الاسم يجب أن يكون نصًا.',
            'name.max'                => 'حقل الاسم يجب ألا يزيد عن 255 حرفًا.',
            'name.unique'             => 'اسم المنتج مستخدم من قبل، يرجى اختيار اسم آخر.',
            'purchase_price.required' => 'حقل سعر الشراء مطلوب.',
            'purchase_price.numeric'  => 'سعر الشراء يجب أن يكون رقمًا.',
            'purchase_price.min'      => 'سعر الشراء يجب ألا يقل عن 0.',
            'sale_price.required'     => 'حقل سعر البيع مطلوب.',
            'sale_price.numeric'      => 'سعر البيع يجب أن يكون رقمًا.',
            'sale_price.min'          => 'سعر البيع يجب ألا يقل عن 0.',
            'stock.required'          => 'حقل المخزون مطلوب.',
            'stock.integer'           => 'المخزون يجب أن يكون عددًا صحيحًا.',
            'stock.min'               => 'المخزون يجب ألا يقل عن 0.',
        ];

        $request->validate($rules, $messages);

        $request_data = $request->all();

        if ($request->image) {
            if ($product->image != 'default.png') {
                Storage::disk('public_uploads')->delete('/product_images/' . $product->image);
            }

            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/product_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();
        }

        $product->update($request_data);

        session()->flash('success', 'تم التعديل بنجاح');
        return redirect()->route('dashboard.products.index');
    }

    public function destroy(Product $product)
    {
        if ($product->image != 'default.png') {
            Storage::disk('public_uploads')->delete('/product_images/' . $product->image);
        }

        $product->delete();
        session()->flash('success', 'تم الحذف بنجاح');
        return redirect()->route('dashboard.products.index');
    }
}
