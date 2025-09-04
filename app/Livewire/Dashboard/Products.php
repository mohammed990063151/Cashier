<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;

class Products extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $category_id = '';
    public $categories;

    public $name, $description, $purchase_price, $sale_price, $stock, $image;

    public $editMode = false;
    public $productId;

    protected $paginationTheme = 'bootstrap';



    public function boot()
    {
        Paginator::useBootstrap();
    }

    protected function rules()
    {
        return [
            'category_id'    => 'required',
            'name' => 'required|string|max:255|unique:products,name,' . $this->productId,
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
        ];
    }
    protected $messages = [
        'category_id.required'    => 'حقل القسم مطلوب.',
        'name.unique'    => 'اسم المنتج مستخدم من قبل، يرجى اختيار اسم آخر.',
        'name.required'           => 'حقل الاسم مطلوب.',
        'name.string'             => 'حقل الاسم يجب أن يكون نصًا.',
        'name.max'                => 'حقل الاسم يجب ألا يزيد عن 255 حرفًا.',
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


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    // مسار الصورة الافتراضية
    public function getImagePathAttribute()
    {
        if (!$this->image) {
            return asset('dashboard_files/img/logo.png');
        }

        $localPath = public_path('uploads/product_images/' . $this->image);
        if (file_exists($localPath)) {
            return asset('uploads/product_images/' . $this->image);
        }

        return asset('dashboard_files/img/logo.png');
    }

    public function render()
    {
        $products = Product::when($this->search, function ($q) {
            $q->where(function ($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('purchase_price', 'like', '%' . $this->search . '%')
                    ->orWhere('sale_price', 'like', '%' . $this->search . '%')
                    ->orWhere('stock', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->category_id, fn($q) => $q->where('category_id', $this->category_id))
            ->latest()
            ->paginate(5);

        return view('livewire.dashboard.products', [
            'products' => $products,
            'categories' => $this->categories,
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'category_id',
            'name',
            'description',
            'purchase_price',
            'sale_price',
            'stock',
            'image',
            'productId',
            'editMode'
        ]);
    }

    public function store()
    {
        $this->validate();

        // تحقق مخصص: سعر البيع >= سعر الشراء
        if ($this->sale_price < $this->purchase_price) {
            $this->addError('sale_price', 'سعر البيع لا يمكن أن يكون أقل من سعر الشراء');
            return;
        }

        $data = [
            'category_id'    => $this->category_id,
            'name'           => $this->name,
            'description'    => $this->description ?? '-',
            'purchase_price' => $this->purchase_price,
            'sale_price'     => $this->sale_price,
            'stock'          => $this->stock,
        ];

        if ($this->image) {
            $imageName = Str::random(10) . '.' . $this->image->getClientOriginalExtension();
            $img = Image::make($this->image->getRealPath())->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save(public_path('uploads/product_images/' . $imageName));
            $data['image'] = $imageName;
        }

        Product::create($data);

        session()->flash('success', 'تمت الإضافة بنجاح');
        $this->dispatch('closeModal');
        $this->resetForm();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $this->productId      = $product->id;
        $this->editMode       = true;
        $this->category_id    = $product->category_id;
        $this->name           = $product->name;
        $this->description    = $product->description;
        $this->purchase_price = $product->purchase_price;
        $this->sale_price     = $product->sale_price;
        $this->stock          = $product->stock;
        $this->image          = null;

        $this->dispatch('openModal');
    }

    public function update()
    {
        $this->validate();

        // تحقق مخصص: سعر البيع >= سعر الشراء
        if ($this->sale_price < $this->purchase_price) {
            $this->addError('sale_price', 'سعر البيع لا يمكن أن يكون أقل من سعر الشراء');
            return;
        }

        try {
            $product = Product::findOrFail($this->productId);
            if ($this->purchase_price != $product->purchase_price) {
                \App\Models\PriceHistory::create([
                    'product_id' => $product->id,
                    'old_price'  => $product->purchase_price,
                    'new_price'  => $this->purchase_price,
                    'type'       => 'Product', // نوع التغيير
                ]);
            }


            $data = [
                'category_id'    => $this->category_id,
                'name'           => $this->name,
                'description'    => $this->description ?? '-',
                'purchase_price' => $this->purchase_price,
                'sale_price'     => $this->sale_price,
                'stock'          => $this->stock,
            ];

            if ($this->image) {
                if ($product->image && $product->image !== 'logo.png') {
                    Storage::disk('public')->delete('uploads/product_images/' . $product->image);
                }

                $imageName = Str::random(10) . '.' . $this->image->getClientOriginalExtension();
                $img = Image::make($this->image->getRealPath())->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save(public_path('uploads/product_images/' . $imageName));
                $data['image'] = $imageName;
            }

            $product->update($data);

            session()->flash('success', 'تم التعديل بنجاح');
            $this->resetForm();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            $this->addError('update_error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && $product->image !== 'logo.png') {
            Storage::disk('public')->delete('uploads/product_images/' . $product->image);
        }

        $product->delete();

        session()->flash('success', 'تم الحذف بنجاح');
    }
}
