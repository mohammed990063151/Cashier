@extends('layouts.dashboard.app')

@section('content')

    <div class="content-wrapper">

        <section class="content-header">
            <h1>المستخدمون</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
                <li class="active">المستخدمون</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title" style="margin-bottom: 15px">
                        المستخدمون <small>{{ $users->total() }}</small>
                    </h3>

                    <form action="{{ route('dashboard.users.index') }}" method="get">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:8px;">
                                <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <button type="submit" class="btn btn-primary" style="margin-bottom:6px;">
                                    <i class="fa fa-search"></i> بحث
                                </button>

                                @if (auth()->user()->hasPermission('create_users'))
                                    <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary" style="margin-bottom:6px;">
                                        <i class="fa fa-plus"></i> إضافة
                                    </a>
                                @else
                                    <a href="#" class="btn btn-primary disabled" style="margin-bottom:6px;">
                                        <i class="fa fa-plus"></i> إضافة
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <div class="box-body">
                    @if ($users->count() > 0)
                        <div class="table-responsive mobile-card-table">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم الأول</th>
                                    <th>الاسم الأخير</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الصورة</th>
                                    <th>الإجراءات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $index=>$user)
                                    <tr>
                                        <td data-label="#">{{ $users->firstItem() + $index }}</td>
                                        <td data-label="الاسم الأول">{{ $user->first_name }}</td>
                                        <td data-label="الاسم الأخير">{{ $user->last_name }}</td>
                                        <td data-label="البريد" style="word-break:break-all;">{{ $user->email }}</td>
                                        <td data-label="الصورة">
                                            <img src="{{ $user->image_path }}" style="width: 64px; max-width:100%;" class="img-thumbnail" alt="">
                                        </td>
                                        <td data-label="الإجراءات">
                                            <div class="phone-action-bar">
                                            @if (auth()->user()->hasPermission('update_users'))
                                                <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i> تعديل
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-info disabled">
                                                    <i class="fa fa-edit"></i> تعديل
                                                </a>
                                            @endif

                                            @if (auth()->user()->hasPermission('delete_users'))
                                                <form action="{{ route('dashboard.users.destroy', $user->id) }}" method="post">
                                                    {{ csrf_field() }}
                                                    {{ method_field('delete') }}
                                                    <button type="submit" class="btn btn-danger delete">
                                                        <i class="fa fa-trash"></i> حذف
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-danger disabled">
                                                    <i class="fa fa-trash"></i> حذف
                                                </button>
                                            @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="products-pagination text-center">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @else
                        <h2>لا توجد بيانات</h2>
                    @endif
                </div>
            </div>
        </section>
    </div>

@endsection
