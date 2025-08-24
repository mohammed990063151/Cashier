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

    protected function rules()
    {
        return [
            'category_id' => 'required',
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'required|integer',
        ];
    }
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

    public function render()
    {
      $products = Product::when($this->search, function ($q) {
    $q->where(function($q2) {
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
//  public function render()
// {
//     $products = Product::with('category')
//         ->when($this->search, function ($q) {
//             $search = $this->search;
//             $q->where(function ($query) use ($search) {
//                 $query->where('name', 'like', "%{$search}%")
//                       ->orWhere('description', 'like', "%{$search}%")
//                       ->orWhere('purchase_price', 'like', "%{$search}%")
//                       ->orWhere('sale_price', 'like', "%{$search}%")
//                       ->orWhere('stock', 'like', "%{$search}%")
//                       ->orWhereHas('category', function ($q2) use ($search) {
//                           $q2->where('name', 'like', "%{$search}%");
//                       });
//             });
//         })
//         ->when($this->category_id, function ($q) {
//             $q->where('category_id', $this->category_id);
//         })
//         ->orderBy('id', 'desc')
//         ->paginate(5);

//     return view('livewire.dashboard.products', [
//         'products' => $products,
//         'categories' => $this->categories,
//     ]);
// }



    public function resetForm()
    {
        $this->reset([
            'category_id', 'name', 'description', 'purchase_price', 'sale_price', 'stock',
            'image', 'productId', 'editMode'
        ]);
    }

    public function store()
    {
        $validated = $this->validate();

        $data = [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description ?? '-',
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'stock' => $this->stock,
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

    try {
        $product = Product::findOrFail($this->productId);

        $data = [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description ?? '-',
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'stock' => $this->stock,
        ];

        if ($this->image) {
            if ($product->image && $product->image !== 'default.png') {
                Storage::disk('public_uploads')->delete('product_images/' . $product->image);
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
        // سجل الخطأ في اللوج
        Log::error('Error updating product: ' . $e->getMessage());

        // اعرض رسالة خطأ داخل المكون
        $this->addError('update_error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
    }
}


    public function delete($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && $product->image !== 'default.png') {
            Storage::disk('public_uploads')->delete('/product_images/' . $product->image);
        }

        $product->delete();

        session()->flash('success', 'تم الحذف بنجاح');
    }
}
