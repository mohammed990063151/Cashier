@extends('layouts.dashboard.app')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>المنتجات</h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
                <li><a href="{{ route('dashboard.products.index') }}"> المنتجات</a></li>
                <li class="active">إضافة</li>
            </ol>
        </section>

        <section class="content">

            <div class="box box-primary">

                <div class="box-header">
                    <h3 class="box-title">إضافة</h3>
                </div><!-- نهاية ترويسة الصندوق -->

                <div class="box-body">

                    @include('partials._errors')

                    <form action="{{ route('dashboard.products.store') }}" method="post" enctype="multipart/form-data">

                        {{ csrf_field() }}
                        {{ method_field('post') }}

                        <div class="form-group">
                            <label>الأقسام</label>
                            <select name="category_id" class="form-control">
                                <option value="">كل الأقسام</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- @foreach (config('translatable.locales') as $locale) --}}
                            <div class="form-group">
                                <label>الاسم </label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            </div>


                        {{-- @endforeach --}}



                        <div class="form-group">
                            <label>سعر الشراء</label>
                            <input type="number" name="purchase_price" step="0.01" class="form-control" value="{{ old('purchase_price') }}">
                        </div>

                        <div class="form-group">
                            <label>سعر البيع</label>
                            <input type="number" name="sale_price" step="0.01" class="form-control" value="{{ old('sale_price') }}">
                        </div>

                        <div class="form-group">
                            <label>الكمية في المخزون</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock') }}">
                        </div>
                         <div class="form-group">
                            <label>الصورة</label>
                            <input type="file" name="image" class="form-control image">
                        </div>

                        <div class="form-group">
                            <img src="{{ asset('uploads/product_images/default.png') }}" style="width: 100px" class="img-thumbnail image-preview" alt="صورة">
                        </div>
                        <div class="form-group">
                                <label>الوصف </label>
                                <textarea name="description" class="form-control ckeditor">{{ old('description') }}</textarea>
                            </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</button>
                        </div>


                    </form><!-- نهاية النموذج -->

                </div><!-- نهاية جسم الصندوق -->

            </div><!-- نهاية الصندوق -->

        </section><!-- نهاية المحتوى -->

    </div><!-- نهاية ملف المحتوى -->

@endsection
