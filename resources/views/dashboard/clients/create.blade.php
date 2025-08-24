@extends('layouts.dashboard.app')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>العملاء</h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
                <li><a href="{{ route('dashboard.clients.index') }}"> العملاء</a></li>
                <li class="active">إضافة</li>
            </ol>
        </section>

        <section class="content">

            <div class="box box-primary">

                <div class="box-header">
                    <h3 class="box-title">إضافة عميل جديد</h3>
                </div><!-- end of box header -->
                <div class="box-body">

                    @include('partials._errors')

                    <form action="{{ route('dashboard.clients.store') }}" method="post">

                        {{ csrf_field() }}
                        {{ method_field('post') }}

                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                       @for ($i = 0; $i < 2; $i++)
                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="text" name="phone[]" class="form-control" value="{{ old('phone.' . $i) }}">
                            </div>
                       @endfor

                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</button>
                        </div>

                    </form><!-- end of form -->

                </div><!-- end of box body -->

            </div><!-- end of box -->

        </section><!-- end of content -->

    </div><!-- end of content wrapper -->

@endsection
