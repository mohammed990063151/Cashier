@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>الموردين
            <small>{{ $suppliers->total() }} مورد</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">الموردين</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">الموردين</h3>
                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <form action="{{ route('dashboard.suppliers.index') }}" method="get">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                    <a href="{{ route('dashboard.suppliers.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> إضافة مورد جديد</a>
                                </div>
                            </div>
                        </form>

                    </div><!-- end of box header -->

                    @if ($suppliers->count() > 0)

                    <div class="box-body table-responsive">

                        <table class="table table-hover">
                            <tr>
                                <th>الاسم</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th>الرصيد</th>
                                <th>الإجراءات</th>
                            </tr>

                            @foreach ($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->address }}</td>
                                <td>{{ number_format($supplier->balance, 2) }}</td>
                                <td>
                                    <a href="{{ route('dashboard.suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> تعديل</a>
                                    <!-- زر فتح مودال إضافة دفعة -->
                                    <a href="{{ route('dashboard.supplier-payments.create', $supplier->id) }}" class="btn btn-success btn-sm">
                                        إضافة دفعة
                                    </a>
                                    <a href="{{ route('dashboard.suppliers.payments', $supplier->id) }}" class="btn btn-info btn-sm">
                                        💰 الدفعات
                                    </a>
                                    {{-- <form action="{{ route('dashboard.suppliers.destroy', $supplier->id) }}" method="post" style="display: inline-block;">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i> حذف</button>
                                    </form> --}}
                                </td>
                            </tr>
                            @endforeach
                        </table>
                        {{ $suppliers->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="box-body">
                        <h3>لا توجد سجلات</h3>
                    </div>
                    @endif
                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content section -->

</div><!-- end of content wrapper -->


@endsection
