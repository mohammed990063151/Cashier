@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>إعدادات الشركة</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header"><h3 class="box-title">تحديث بيانات الشركة</h3></div>
            <div class="box-body">
                @include('partials._errors')
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('dashboard.settings.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')

                    <div class="form-group">
                        <label>اسم الشركة</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $setting->name ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>لوجو الشركة</label>
                        <input type="file" name="logo" class="form-control">
                        @if(isset($setting->logo))
                            <img src="{{ asset('storage/'.$setting->logo) }}" alt="Logo" style="width: 120px; margin-top: 10px;">
                        @endif
                    </div>

                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>العنوان</label>
                        <textarea name="address" class="form-control">{{ old('address', $setting->address ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Facebook</label>
                        <input type="url" name="facebook" class="form-control" value="{{ old('facebook', $setting->facebook ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>Twitter</label>
                        <input type="url" name="twitter" class="form-control" value="{{ old('twitter', $setting->twitter ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>Instagram</label>
                        <input type="url" name="instagram" class="form-control" value="{{ old('instagram', $setting->instagram ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $setting->linkedin ?? '') }}">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> حفظ</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>
@endsection
