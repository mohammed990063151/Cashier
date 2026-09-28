@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>إعدادات واتساب</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">مفاتيح الربط</h3>
            </div>
            <div class="box-body">
                @include('partials._errors')
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('dashboard.settings.whatsapp.update') }}" method="post">
                    @csrf
                    @method('put')

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="whatsapp_enabled" value="1" {{ old('whatsapp_enabled', $setting->whatsapp_enabled ?? false) ? 'checked' : '' }}>
                            تفعيل إرسال رسائل الطلبات
                        </label>
                    </div>

                    <div class="form-group">
                        <label>عنوان خدمة واتساب</label>
                        <input type="url" name="whatsapp_base_url" class="form-control" placeholder="http://76.13.77.29:3001" value="{{ old('whatsapp_base_url', $setting->whatsapp_base_url ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="whatsapp_username" class="form-control" value="{{ old('whatsapp_username', $setting->whatsapp_username ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>كلمة السر</label>
                        <input type="password" name="whatsapp_password" class="form-control" placeholder="{{ filled($setting->whatsapp_password ?? null) ? 'محفوظة، اتركها فارغة إن لم ترد تغييرها' : '' }}">
                    </div>

                    <div class="form-group">
                        <label>معرّف الجهاز (Device ID)</label>
                        <input type="text" name="whatsapp_device_id" class="form-control" value="{{ old('whatsapp_device_id', $setting->whatsapp_device_id ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>رقم واتساب المستخدم داخل النظام</label>
                        <input type="text" name="whatsapp_staff_phone" class="form-control" placeholder="9665xxxxxxxx" value="{{ old('whatsapp_staff_phone', $setting->whatsapp_staff_phone ?? '') }}">
                        <p class="help-block">تصل إليه نسخة من كل طلب جديد، وإليه تُرسل رسالة الاختبار.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <a href="{{ route('dashboard.settings.whatsapp.logs') }}" class="btn btn-default">سجل الرسائل</a>
                </form>

                <hr>
                <form action="{{ route('dashboard.settings.whatsapp.test') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-success">إرسال رسالة اختبار</button>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
