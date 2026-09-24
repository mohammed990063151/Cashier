@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">

        <h1>التصنيفات</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">التصنيفات</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary">

            <div class="box-header with-border">

                <h3 class="box-title" style="margin-bottom: 15px">التصنيفات <small>{{ $categories->total() }}</small></h3>

                <form action="{{ route('dashboard.categories.index') }}" method="get">

                    <div class="row">

                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                            @if (auth()->user()->hasPermission('create_categories'))
                                <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</a>
                            @else
                                <a href="#" class="btn btn-primary disabled"><i class="fa fa-plus"></i> إضافة</a>
                            @endif
                        </div>

                    </div>
                </form><!-- end of form -->

            </div><!-- end of box header -->

            <div class="box-body">

                @if ($categories->count() > 0)

                    <div class="table-responsive mobile-card-table">
                    <table class="table table-hover">

                        <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>عدد المنتجات</th>
                            <th>المنتجات المرتبطة</th>
                            <th>الإجراءات</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($categories as $index => $category)
                            <tr>
                                <td data-label="#">{{ $categories->firstItem() + $index }}</td>
                                <td data-label="الاسم"><strong>{{ $category->name }}</strong></td>
                                <td data-label="عدد المنتجات">{{ $category->products->count() }}</td>
                                <td data-label="المنتجات">
                                    <a href="{{ route('dashboard.products.index', ['category_id' => $category->id]) }}" class="btn btn-info btn-block">المنتجات المرتبطة</a>
                                </td>
                                <td data-label="الإجراءات">
                                    <div class="phone-action-bar">
                                    @if (auth()->user()->hasPermission('update_categories'))
                                        <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i> تعديل
                                        </a>
                                    @else
                                        <a href="#" class="btn btn-info disabled">
                                            <i class="fa fa-edit"></i> تعديل
                                        </a>
                                    @endif

                                    @if (auth()->user()->hasPermission('delete_categories'))
                                        <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="post">
                                            {{ csrf_field() }}
                                            {{ method_field('delete') }}
                                            <button type="submit" class="btn btn-danger delete"><i class="fa fa-trash"></i> حذف</button>
                                        </form>
                                    @else
                                        <button class="btn btn-danger disabled"><i class="fa fa-trash"></i> حذف</button>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                    </div>

                    <div class="products-pagination text-center">
                        {{ $categories->appends(request()->query())->links() }}
                    </div>
                @else

                    <h2>لا توجد بيانات</h2>

                @endif

            </div><!-- end of box body -->

        </div><!-- end of box -->

    </section><!-- end of content -->

</div><!-- end of content wrapper -->

@endsection
