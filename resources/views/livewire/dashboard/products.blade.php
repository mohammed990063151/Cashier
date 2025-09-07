<div class="content-wrapper">
    <section class="content-header">
        <h1>المنتجات</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المنتجات</li>
        </ol>
    </section>

    <section class="content p-0">
        <div class="box box-primary">
            <div class="box-header with-border d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="box-title mb-3">
                    المنتجات
                    <small class="badge" style="background-color: #ff9800; color: #080808;">
                        {{ $products->total() }}
                    </small>
                </h5>

                @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade in w-100" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <strong>نجاح!</strong> {{ session('success') }}
                </div>
                @endif
                <br /><br /><br />
                <div class="row w-100">
                    <div class="col-sm-12 col-md-4 mb-2">
                        <input type="text" id="searchInput" wire:model.debounce.500ms="search" placeholder="بحث في كل الأعمدة" class="form-control">
                    </div>

                    <div class="col-sm-12 col-md-4 mb-2">
                        <select wire:model="category_id" class="form-control">
                            <option value="">كل الأقسام</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-4 mb-2 text-md-end">
                        @if (auth()->user()->hasPermission('create_products'))
                        <button class="btn btn-primary w-100" data-toggle="modal" data-target="#productModal">
                            {{ $editMode ? 'تعديل المنتج' : 'إضافة منتج' }}
                        </button>
                        @else
                        <a href="#" class="btn btn-primary w-100 disabled"><i class="fa fa-plus"></i> إضافة</a>
                        @endif
                    </div>
                </div>
            </div>
            <br />
            <div class="box-body">
                @if ($products->count() > 0)
                <div class="table-responsive">
                    <table id="productsTable" class="table table-hover table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الوصف</th>
                                <th>القسم</th>
                                <th>الصورة</th>
                                <th>سعر الشراء</th>
                                <th>سعر البيع</th>
                                <th>نسبة الربح %</th>
                                <th>المخزون</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $product->name ?? "-" }}</td>
                                <td>{!! $product->description ?? "-" !!}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>
                                    {{-- <img src="{{ asset('storage/product_images/' . $product->image) }}" style="width: 80px" class="img-thumbnail" alt="صورة المنتج"> --}}
                                    @if($product->image)
    <img src="{{ asset('storage/product_images/' . $product->image) }}" style="width: 80px" class="img-thumbnail" alt="صورة المنتج">
@else
    <img src="{{ asset('dashboard_files/img/logo.png') }}" style="width: 80px" class="img-thumbnail" alt="صورة المنتج">
@endif

                                </td>
                                <td>{{ $product->purchase_price }}</td>
                                <td>{{ $product->sale_price }}</td>
                                <td>{{ $product->profit_percent }} %</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    @if (auth()->user()->hasPermission('update_products'))
                                    <button wire:click="edit({{ $product->id }})" class="btn btn-info btn-sm mb-1">
                                        <i class="fa fa-edit"></i> تعديل
                                    </button>
                                    @else
                                    <button class="btn btn-info btn-sm disabled mb-1">
                                        <i class="fa fa-edit"></i> تعديل
                                    </button>
                                    @endif
                                    {{-- @if (auth()->user()->hasPermission('delete_products'))
                                                <form action="{{ route('dashboard.products.destroy', $product->id) }}"
                                    method="post" style="display:inline-block">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i> حذف
                                    </button>
                                    </form>
                                    @else
                                    <button class="btn btn-danger btn-sm disabled">
                                        <i class="fa fa-trash"></i> حذف
                                    </button>
                                    @endif --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>

                @else
                <h4 class="text-center text-muted">لا توجد بيانات</h4>
                @endif
            </div>
        </div>
    </section>

    {{-- ✅ المودال داخل نفس الـ content-wrapper --}}
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: {{ $editMode ? '#007bff' : 'orange' }};">
                    <h3 class="modal-title">{{ $editMode ? 'تعديل المنتج' : 'إضافة منتج' }}</h3>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body row">
                    <div class="form-group col-md-6">
                        <label>القسم</label>
                        <select wire:model="category_id" class="form-control">
                            <option value="">اختر القسم</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>الاسم</label>
                        <input wire:model.defer="name" type="text" class="form-control">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-8">
                        <label>الوصف</label>
                        <textarea wire:model.defer="description" class="form-control"></textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>سعر الشراء</label>
                        <input wire:model.defer="purchase_price" type="number" class="form-control">
                        @error('purchase_price') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>سعر البيع</label>
                        <input wire:model.defer="sale_price" type="number" class="form-control">
                        @error('sale_price') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>المخزون</label>
                        <input wire:model.defer="stock" type="number" class="form-control">
                        @error('stock') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-12">
                        <label>الصورة</label>
                        <input wire:model="image" type="file" class="form-control">
                        @error('image') <span class="text-danger">{{ $message }}</span> @enderror

                        @if ($image instanceof \Livewire\TemporaryUploadedFile)
                        <img src="{{ $image->temporaryUrl() }}" width="100" class="img-thumbnail mt-2">
                        @elseif($editMode && $productId)
                        @php $oldImage = \App\Models\Product::find($productId)->image ?? null; @endphp
                        @if($oldImage)
                        <img src="{{ asset('uploads/product_images/' . $oldImage) }}" width="100" class="img-thumbnail mt-2">
                        @endif
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    {{-- <button wire:click.prevent="{{ $editMode ? 'update' : 'store' }}" class="btn btn-primary">
                    {{ $editMode ? 'تحديث' : 'إضافة' }}
                    </button> --}}
                    @if($editMode)
                    <button wire:click.prevent="update" class="btn btn-primary">تحديث</button>
                    @else
                    <button wire:click.prevent="store" class="btn btn-primary">إضافة</button>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('openModal', () => $('#productModal').modal('show'));
    window.addEventListener('closeModal', () => $('#productModal').modal('hide'));

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#productsTable tbody tr');
        input.addEventListener('keyup', function() {
            const filter = input.value.toLowerCase();
            rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none');
        });
    });

</script>
@endpush
