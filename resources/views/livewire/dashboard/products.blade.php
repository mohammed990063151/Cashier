<div class="content-wrapper">
    <section class="content-header">
        <h1>المنتجات</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المنتجات</li>
        </ol>
    </section>

    <section class="content" style="
    padding: 0 !important;
">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h5 class="box-title" style="margin-bottom: 15px;">
                    المنتجات
                    <small style="background-color: #ff9800; color: #080808; padding: 2px 8px; border-radius: 5px;">
                        {{ $products->total() }}
                    </small>
                </h5>



                <div class="row">
                    <div class="col-md-4">
                    <input type="text" id="searchInput" wire:model.debounce.500ms="search" placeholder="بحث في كل الأعمدة" class="form-control">




                    </div>


                    <div class="col-md-4">
                        <select wire:model="category_id" class="form-control">
                            <option value="">كل الأقسام</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        @if (auth()->user()->hasPermission('create_products'))
                        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#productModal">
                            {{ $editMode ? 'تعديل المنتج' : 'إضافة منتج' }}
                        </button>

                        @else
                        <a href="#" class="btn btn-primary disabled"><i class="fa fa-plus"></i> إضافة</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="box-body">
                @if ($products->count() > 0)
                <table id="productsTable" class="table table-hover">
                    <thead>
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
                            <td><img src="{{ $product->image_path }}" style="width: 100px" class="img-thumbnail"></td>
                            <td>{{ $product->purchase_price }}</td>
                            <td>{{ $product->sale_price }}</td>
                            <td>{{ $product->profit_percent }} %</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                @if (auth()->user()->hasPermission('update_products'))
                                {{-- <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> تعديل</a> --}}
                                {{-- <button wire:click="edit({{ $product->id }})" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> تعديل</button> --}}

                                <button wire:click="edit({{ $product->id }})" class="btn btn-info btn-sm">
                                    <i class="fa fa-edit"></i> تعديل
                                </button>
                                @else
                                <a href="#" class="btn btn-info btn-sm disabled"><i class="fa fa-edit"></i> تعديل</a>
                                @endif
                                @if (auth()->user()->hasPermission('delete_products'))
                                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post" style="display:inline-block">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger delete btn-sm"><i class="fa fa-trash"></i> حذف</button>
                                </form>
                                @else
                                <button class="btn btn-danger btn-sm disabled"><i class="fa fa-trash"></i> حذف</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $products->links() }}
                @else
                <h2>لا توجد بيانات</h2>
                @endif
                <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                        <div class="modal-header" style="background-color: {{ $editMode ? '#007bff' : 'orange' }}; color: white;">
                            <h3 class="modal-title" id="productModalLabel">
                                {{ $editMode ? 'تعديل المنتج' : 'إضافة منتج' }}
                            </h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>


                        <div class="modal-body">

                            <div class="form-group">
                                <label>القسم</label>
                                <select wire:model="category_id" class="form-control">
                                    <option value="">اختر القسم</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>الاسم</label>
                                <input wire:model.defer="name" type="text" class="form-control">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>الوصف</label>
                                <textarea wire:model.defer="description" class="form-control"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>سعر الشراء</label>
                                <input wire:model.defer="purchase_price" type="number" class="form-control">
                                @error('purchase_price') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>سعر البيع</label>
                                <input wire:model.defer="sale_price" type="number" class="form-control">
                                @error('sale_price') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>المخزون</label>
                                <input wire:model.defer="stock" type="number" class="form-control">
                                @error('stock') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            {{-- <div class="form-group">
                                    <label>الصورة</label>
                                    <input wire:model="image" type="file" class="form-control">
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror

                            @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" width="100" class="img-thumbnail mt-2">
                            @endif
                            @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" width="100" class="img-thumbnail mt-2">
                            @endif

                        </div> --}}
                        <div class="form-group">
                            <label>الصورة</label>
                            <input wire:model="image" type="file" class="form-control">
                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror

                            @if ($image instanceof \Livewire\TemporaryUploadedFile)
                            <!-- الصورة الجديدة عند رفع ملف -->
                            <img src="{{ $image->temporaryUrl() }}" width="100" class="img-thumbnail mt-2">
                            @elseif($editMode && $productId)
                            <!-- عرض الصورة القديمة عند التعديل -->
                            @php
                            $oldImage = \App\Models\Product::find($productId)->image ?? null;
                            @endphp
                            @if($oldImage)
                            <img src="{{ asset('uploads/product_images/' . $oldImage) }}" width="100" class="img-thumbnail mt-2">
                            @endif
                            @endif
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button wire:click.prevent="{{ $editMode ? 'update' : 'store' }}" class="btn btn-primary">
                            {{ $editMode ? 'تحديث' : 'إضافة' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>



</div>
</div>

<!-- Button to open modal -->

<!-- Modal -->


</section>
</div>


@push('scripts')
<script>
    window.addEventListener('openModal', () => {
        $('#productModal').modal('show');
    });

    window.addEventListener('closeModal', () => {
        $('#productModal').modal('hide');
    });

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('searchInput');
    const table = document.getElementById('productsTable');
    const rows = table.querySelectorAll('tbody tr');
    input.addEventListener('keyup', function() {
        const filter = input.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
</script>

@endpush
