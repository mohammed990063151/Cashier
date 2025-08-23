<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم | تسجيل الدخول</title>

    {{-- Bootstrap 3.3.7 --}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/skin-blue.min.css') }}">

    @if (app()->getLocale() == 'ar')
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/font-awesome-rtl.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/AdminLTE-rtl.min.css') }}">
        <link href="https://fonts.googleapis.com/css?family=Cairo:400,700" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/bootstrap-rtl.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/rtl.css') }}">

        <style>
            body, h1, h2, h3, h4, h5, h6 {
                font-family: 'Cairo', sans-serif !important;
            }
        </style>
    @else
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dashboard_files/css/AdminLTE.min.css') }}">
    @endif

    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

</head>
<body class="login-page">

<div class="login-box">

    <div class="login-logo">
        <a href="#"><b>لوحة</b> التحكم</a>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">قم بتسجيل الدخول لبدء جلستك</p>

        <form action="{{ route('login') }}" method="post">
            {{ csrf_field() }}
            {{ method_field('post') }}

            @include('partials._errors')

            <div class="form-group has-feedback">
                <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="كلمة المرور">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="form-group">
                <label style="font-weight: normal;">
                    <input type="checkbox" name="remember"> تذكرني
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-flat">تسجيل الدخول</button>

        </form>

    </div>

</div>

<script src="{{ asset('dashboard_files/js/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard_files/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('dashboard_files/plugins/icheck/icheck.min.js') }}"></script>
<script src="{{ asset('dashboard_files/js/fastclick.js') }}"></script>

</body>
</html>
