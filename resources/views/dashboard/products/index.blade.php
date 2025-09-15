@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>المنتجات</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المنتجات</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary">

            <div class="box-header with-border">
                <h3 class="box-title" style="margin-bottom: 15px">المنتجات <small>{{ $products->count() }}</small></h3>

                <form action="{{ route('dashboard.products.index') }}" method="get">
                    <div class="row">

                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                        </div>

                        <div class="col-md-4">
                            <select name="category_id" class="form-control">
                                <option value="">كل الأقسام</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                            @if (auth()->user()->hasPermission('create_products'))
                                <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</a>
                            @else
                                <a href="#" class="btn btn-primary disabled"><i class="fa fa-plus"></i> إضافة</a>
                            @endif
                        </div>

                    </div>
                </form>
            </div><!-- end of box header -->

            <div class="box-body">

                @if ($products->count() > 0)
                    <div class="table-responsive">
                        <table id="products-table" class="table table-hover table-bordered table-striped nowrap" style="width:100%">
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
                                @foreach ($products as $index=>$product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{!! $product->description !!}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td><img src="{{ $product->image_path }}" style="width: 100px"  class="img-thumbnail" alt=""></td>
                                    <td>{{ $product->purchase_price }}</td>
                                    <td>{{ $product->sale_price }}</td>
                                    <td>{{ $product->profit_percent }} %</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        @if (auth()->user()->hasPermission('update_products'))
                                            <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> تعديل</a>
                                        @else
                                            <a href="#" class="btn btn-info btn-sm disabled"><i class="fa fa-edit"></i> تعديل</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_products'))
                                            <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post" style="display: inline-block">
                                                {{ csrf_field() }}
                                                {{ method_field('delete') }}
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
                    </div><!-- end of table -->
                @else
                    <h2>لا توجد بيانات</h2>
                @endif

            </div><!-- end of box body -->

        </div><!-- end of box -->

    </section><!-- end of content -->

</div><!-- end of content wrapper -->

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Responsive -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#products-table').DataTable({
        responsive: true, // دعم الشاشات الصغيرة
        paging: true,
        searching: true,
        ordering: true,
        order: [[0, 'desc']], // ترتيب افتراضي حسب العمود الأول
        info: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/Arabic.json"
        },
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5',
            {
                extend: 'print',
                text: 'طباعة',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ]
    });
});
</script>
@endpush

@endsection
