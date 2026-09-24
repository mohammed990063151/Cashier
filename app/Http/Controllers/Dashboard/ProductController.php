<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $products = $this->productService->listForIndex($request);

        return view('dashboard.products.index', compact('categories', 'products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('dashboard.products.create', compact('categories'));
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->whereNull('deleted_at'),
            ],
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'pieces_per_carton' => 'nullable|integer|min:1',
            'sale_mode' => 'nullable|in:piece_only,bulk_only,flexible',
            'measure_unit' => 'nullable|in:piece,carton,kilo',
        ], $this->messages());

        $validated['sale_price'] = $validated['sale_price']
            ?? round((float) $validated['purchase_price'] * 1.15, 3);
        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['measure_unit'] = $validated['measure_unit'] ?? 'piece';
        $validated['sale_mode'] = $validated['sale_mode'] ?? 'flexible';

        $product = Product::create(
            array_merge($this->productService->preparePayload($validated), ['image' => Product::DEFAULT_IMAGE])
        );

        return response()->json([
            'message' => 'تم إضافة المنتج.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
                'pieces_per_carton' => max(1, (int) $product->pieces_per_carton),
                'sale_mode' => $product->sale_mode,
                'measure_unit' => $product->measure_unit,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $payload = $this->productService->preparePayload($validated);

        if ($request->hasFile('image')) {
            $payload['image'] = $this->storeImage($request->file('image'));
        } else {
            $payload['image'] = Product::DEFAULT_IMAGE;
        }

        Product::create($payload);

        session()->flash('success', 'تم إضافة المنتج بنجاح');

        return redirect()->route('dashboard.products.index');
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('dashboard.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('dashboard.products.edit', compact('categories', 'product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate($this->rules($product->id), $this->messages());

        $payload = $this->productService->preparePayload($validated);

        if ($request->hasFile('image')) {
            $this->deleteImageFile($product->image);
            $payload['image'] = $this->storeImage($request->file('image'));
        }

        $product->update($payload);

        session()->flash('success', 'تم تحديث المنتج بنجاح');

        return redirect()->route('dashboard.products.index');
    }

    public function destroy(Product $product)
    {
        if (! $this->productService->canDelete($product)) {
            session()->flash('error', 'لا يمكن حذف منتج مرتبط بطلبات سابقة. يمكنك تعديل المخزون أو إخفاؤه لاحقاً.');

            return redirect()->route('dashboard.products.index');
        }

        $this->deleteImageFile($product->image);
        $product->delete();

        session()->flash('success', 'تم حذف المنتج بنجاح');

        return redirect()->route('dashboard.products.index');
    }

    /**
     * @return array<string, string>
     */
    protected function rules(?int $productId = null): array
    {
        $uniqueName = Rule::unique('products', 'name')->whereNull('deleted_at');
        if ($productId) {
            $uniqueName = $uniqueName->ignore($productId);
        }

        return [
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'pieces_per_carton' => 'nullable|integer|min:1',
            'sale_mode' => 'nullable|in:piece_only,bulk_only,flexible',
            'measure_unit' => 'required|in:piece,carton,kilo',
            'image' => 'nullable|image|max:2048',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'category_id.required' => 'اختر القسم.',
            'category_id.exists' => 'القسم غير موجود.',
            'name.required' => 'اسم المنتج مطلوب.',
            'name.unique' => 'اسم المنتج مستخدم مسبقاً.',
            'purchase_price.required' => 'سعر الشراء مطلوب.',
            'sale_price.required' => 'سعر البيع مطلوب.',
            'stock.required' => 'المخزون مطلوب.',
            'measure_unit.required' => 'وحدة القياس مطلوبة.',
            'measure_unit.in' => 'وحدة القياس غير صالحة.',
            'sale_mode.in' => 'طريقة البيع غير صالحة.',
            'image.image' => 'الملف يجب أن يكون صورة.',
            'image.max' => 'حجم الصورة كبير جداً (الحد 2 ميجا).',
        ];
    }

    protected function storeImage($file): string
    {
        $filename = $file->hashName();

        Image::make($file)
            ->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save(public_path('uploads/product_images/'.$filename));

        return $filename;
    }

    protected function deleteImageFile(?string $image): void
    {
        if ($image && ! in_array($image, [Product::DEFAULT_IMAGE, Product::LEGACY_DEFAULT_IMAGE], true)) {
            $path = public_path('uploads/product_images/'.$image);
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
