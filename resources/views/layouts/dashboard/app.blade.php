{{-- <!DOCTYPE html> --}}
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $setting->name ?? '' }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--<!-- Bootstrap 3.3.7 -->--}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/skin-blue.min.css') }}">

    {{-- @if (app()->getLocale() == 'ar') --}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/font-awesome-rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/AdminLTE-rtl.min.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Cairo:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/bootstrap-rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/rtl.css') }}">
@livewireStyles
    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Cairo', sans-serif !important;
        }

    </style>
    {{-- @else
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_files/css/AdminLTE.min.css') }}">
    @endif --}}

    <style>


        .mr-2 {
            margin-right: 5px;
        }

        /* ===== هاتف فقط: واجهة لمس واضحة ===== */
        @media (max-width: 767px) {
            html, body {
                -webkit-text-size-adjust: 100%;
                overscroll-behavior-y: contain;
            }
            body {
                font-size: 15px;
                line-height: 1.45;
            }

            /* رأس وشريط جانبي */
            .main-header .logo {
                width: auto !important;
                max-width: 58%;
                padding: 0 8px;
                font-size: 14px !important;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .main-header .navbar {
                min-height: 50px;
            }
            .main-header .sidebar-toggle {
                padding: 14px 16px !important;
                font-size: 18px;
            }
            .navbar-custom-menu > .nav > li > a {
                padding: 14px 12px !important;
                min-width: 44px;
                text-align: center;
            }
            .main-sidebar {
                padding-top: 50px;
            }
            .sidebar-menu > li > a {
                min-height: 48px;
                display: flex !important;
                align-items: center;
                gap: 8px;
                font-size: 15px !important;
                padding: 12px 15px !important;
            }
            .sidebar-menu > li > a > .fa {
                font-size: 16px;
                width: 22px;
                text-align: center;
            }
            .user-panel {
                padding: 14px 10px;
            }
            .user-panel .info {
                font-size: 14px;
            }
            .content-wrapper,
            .right-side {
                margin-right: 0 !important;
                margin-left: 0 !important;
                padding-bottom: 96px;
            }
            .content-header {
                padding: 10px 12px 4px !important;
            }
            .content-header > h1 {
                font-size: 20px !important;
                margin: 0 0 6px !important;
                line-height: 1.3;
            }
            .content-header > h1 > small {
                display: inline-block;
                font-size: 13px;
                margin-right: 6px;
            }
            .breadcrumb { display: none !important; }
            .content {
                padding: 8px 10px 20px !important;
            }
            .main-footer {
                margin-right: 0 !important;
                margin-left: 0 !important;
                padding: 10px;
                font-size: 12px;
                text-align: center;
            }

            /* صناديق وأزرار */
            .box {
                border-radius: 12px;
                margin-bottom: 14px;
                box-shadow: 0 1px 4px rgba(0,0,0,.06);
            }
            .box-header {
                padding: 12px !important;
            }
            .box-header .box-title {
                font-size: 16px !important;
                display: block;
                float: none !important;
                margin: 0 0 10px !important;
                line-height: 1.4;
            }
            .box-header .box-tools {
                float: none !important;
                position: static !important;
                width: 100%;
            }
            .box-header .box-tools .btn,
            .box-header > .btn,
            .box-header .pull-left > .btn,
            .box-header .pull-right > .btn {
                display: block;
                width: 100%;
                margin: 0 0 8px !important;
                float: none !important;
            }
            .box-body {
                padding: 12px !important;
            }
            .box-footer {
                padding: 12px !important;
            }

            /* أهداف لمس كبيرة + منع تكبير iOS */
            .form-control,
            select.form-control,
            textarea.form-control,
            .input-group .form-control {
                min-height: 46px !important;
                height: auto !important;
                font-size: 16px !important;
                border-radius: 10px !important;
                padding: 10px 12px !important;
            }
            .btn {
                min-height: 44px;
                padding: 10px 14px !important;
                font-size: 15px !important;
                border-radius: 10px !important;
                font-weight: 600;
            }
            .btn-sm {
                min-height: 42px;
                padding: 9px 12px !important;
                font-size: 14px !important;
            }
            .btn-xs {
                min-height: 38px;
                padding: 8px 10px !important;
                font-size: 13px !important;
            }
            .btn-block { width: 100%; }
            label {
                font-size: 14px;
                font-weight: 700;
                margin-bottom: 6px;
            }
            .form-group { margin-bottom: 14px; }
            .input-group-btn .btn,
            .input-group-addon {
                min-height: 46px;
                border-radius: 10px !important;
            }

            /* فلاتر قابلة للطي */
            .mobile-filters-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                margin-bottom: 10px;
                background: #f1f5f9;
                border: 1px solid #cbd5e1;
                color: #1e3c72;
            }
            .mobile-filters-toggle .caret-icon {
                transition: transform .2s ease;
            }
            .mobile-filters-toggle.is-open .caret-icon {
                transform: rotate(180deg);
            }
            .mobile-filters-body {
                display: none;
                margin-bottom: 12px;
            }
            .mobile-filters-body.is-open {
                display: block;
            }
            .products-toolbar,
            .orders-toolbar,
            .client-history-filters,
            .mobile-filter-panel {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 12px;
            }
            .products-toolbar .row > [class*="col-"],
            .orders-toolbar .row > [class*="col-"],
            .client-history-filters .row > [class*="col-"] {
                width: 100% !important;
                float: none !important;
                padding-left: 0;
                padding-right: 0;
                margin-bottom: 10px;
            }

            /* جداول → بطاقات */
            .table-responsive,
            .mobile-card-table {
                border: 0;
                overflow-x: visible !important;
            }
            .mobile-card-table table,
            .box-body .table-responsive > table.table,
            .box-body > table.table,
            .dataTables_wrapper table.table {
                display: block;
                width: 100%;
            }
            .mobile-card-table thead,
            .box-body .table-responsive > table.table thead,
            .box-body > table.table thead,
            .dataTables_wrapper table.table thead,
            .mobile-card-table tr.mobile-hide-head {
                display: none !important;
            }
            .mobile-card-table tbody,
            .box-body .table-responsive > table.table tbody,
            .box-body > table.table tbody,
            .dataTables_wrapper table.table tbody {
                display: block;
                width: 100%;
            }
            .mobile-card-table tr,
            .box-body .table-responsive > table.table > tbody > tr,
            .box-body > table.table > tbody > tr,
            .dataTables_wrapper table.table > tbody > tr {
                display: block;
                width: 100%;
                margin-bottom: 14px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #fff;
                padding: 0;
                box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
                overflow: hidden;
            }
            .mobile-card-table td,
            .box-body .table-responsive > table.table > tbody > tr > td,
            .box-body > table.table > tbody > tr > td,
            .dataTables_wrapper table.table > tbody > tr > td {
                display: flex !important;
                width: 100% !important;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
                border: none !important;
                border-bottom: 1px solid #f1f5f9 !important;
                padding: 11px 14px !important;
                text-align: right !important;
                white-space: normal !important;
                word-break: break-word;
                font-size: 14px;
                color: #0f172a;
            }
            .mobile-card-table td:before,
            .box-body .table-responsive > table.table > tbody > tr > td[data-label]:before,
            .box-body > table.table > tbody > tr > td[data-label]:before,
            .dataTables_wrapper table.table > tbody > tr > td[data-label]:before {
                content: attr(data-label);
                font-weight: 700;
                color: #64748b;
                font-size: 12px;
                flex: 0 0 36%;
                max-width: 42%;
                text-align: right;
                margin: 0;
                line-height: 1.4;
            }
            .mobile-card-table td > *:not(script),
            .box-body .table-responsive > table.table > tbody > tr > td > *:not(script) {
                flex: 1 1 auto;
                text-align: right;
                min-width: 0;
            }
            .mobile-card-table td:first-child,
            .box-body .table-responsive > table.table > tbody > tr > td:first-child,
            .box-body > table.table > tbody > tr > td:first-child,
            .dataTables_wrapper table.table > tbody > tr > td:first-child {
                background: #f8fafc;
                font-weight: 700;
                font-size: 15px;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            .mobile-card-table td:last-child,
            .box-body .table-responsive > table.table > tbody > tr > td:last-child,
            .box-body > table.table > tbody > tr > td:last-child,
            .dataTables_wrapper table.table > tbody > tr > td:last-child {
                border-bottom: none !important;
                flex-wrap: wrap;
                background: #fafbfc;
            }
            .mobile-card-table td:last-child:before,
            .box-body .table-responsive > table.table > tbody > tr > td:last-child:before,
            .box-body > table.table > tbody > tr > td:last-child:before {
                display: none;
            }
            .mobile-card-table td .btn,
            .box-body .table .btn,
            .products-actions .btn,
            .orders-actions .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 4px 2px !important;
                min-width: 44px;
                flex: 1 1 calc(33.33% - 6px);
            }
            .mobile-card-table img,
            .box-body .table img {
                max-width: 56px !important;
                height: auto !important;
                border-radius: 8px;
            }
            .products-actions,
            .orders-actions {
                display: flex !important;
                width: 100%;
                flex-wrap: wrap;
                gap: 6px;
            }

            /* DataTables على الجوال */
            .dt-buttons,
            .dataTables_length,
            .dataTables_filter,
            div.dt-button-collection {
                display: none !important;
            }
            .dataTables_wrapper .dataTables_info {
                float: none !important;
                text-align: center;
                padding: 8px 0;
                font-size: 12px;
            }
            .dataTables_wrapper .dataTables_paginate {
                float: none !important;
                text-align: center;
                padding: 8px 0 4px;
            }
            .dataTables_wrapper .paginate_button {
                min-height: 36px !important;
                padding: 6px 10px !important;
            }

            /* شبكة الأعمدة */
            .row > [class*="col-md-"],
            .row > [class*="col-lg-"],
            .row > [class*="col-sm-"] {
                width: 100% !important;
                float: none !important;
                padding-left: 8px;
                padding-right: 8px;
            }
            .small-box {
                margin-bottom: 12px;
            }
            .small-box h3 {
                font-size: 22px !important;
            }
            .small-box p {
                font-size: 14px;
            }
            .info-box {
                min-height: 70px;
                margin-bottom: 12px;
            }

            /* نماذج الطلبات / الفواتير */
            .order-unit-grid {
                flex-direction: column !important;
            }
            .order-unit-block {
                min-width: 100% !important;
                flex: 1 1 100% !important;
            }
            .order-unit-block .unit-qty,
            .order-unit-block .unit-price {
                min-height: 44px !important;
                height: 44px !important;
                font-size: 16px !important;
            }
            .unit-switch {
                grid-template-columns: 1fr !important;
            }
            .modal-dialog {
                width: auto !important;
                margin: 10px !important;
            }
            .modal-content {
                border-radius: 12px;
            }
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 14px !important;
            }
            .modal-footer .btn {
                width: 100%;
                margin: 0 0 8px !important;
            }
            .nav-tabs > li {
                float: none;
                display: block;
                width: 100%;
                margin: 0 0 4px;
            }
            .nav-tabs > li > a {
                border-radius: 8px !important;
                margin: 0;
                min-height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .pagination > li > a,
            .pagination > li > span {
                min-width: 40px;
                min-height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .alert {
                border-radius: 10px;
                font-size: 14px;
            }
            .label {
                font-size: 12px !important;
                padding: 5px 8px !important;
                border-radius: 6px;
                display: inline-block;
                margin: 2px 0;
                white-space: normal;
                line-height: 1.35;
            }
            .select2-container .select2-selection--single {
                min-height: 46px !important;
                padding-top: 8px;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                font-size: 16px;
                line-height: 28px;
            }

            /* مساعد ذكي بعيداً عن المحتوى */
            #ai-assistant-toggle.ai-fab,
            .ai-fab {
                bottom: 18px !important;
                left: 14px !important;
                z-index: 1040;
            }
            .ai-panel {
                left: 8px !important;
                right: 8px !important;
                width: auto !important;
                bottom: 78px !important;
                max-height: 70vh;
            }

            .panel-heading {
                padding: 14px 12px !important;
            }
            .panel-title {
                font-size: 15px !important;
            }
            .panel-title > a {
                display: block;
                min-height: 28px;
                padding: 4px 0;
            }
            .panel-body {
                padding: 10px !important;
            }
            /* زر حفظ ثابت أسفل الشاشة في النماذج */
            .box-footer .btn-primary,
            #add-order-form-btn,
            form .btn-primary.btn-block {
                min-height: 48px;
                font-size: 16px !important;
            }

            /* إخفاء عناصر سطح المكتب المزعجة */
            .hidden-xs-phone { display: none !important; }
            .visible-phone-block { display: block !important; }
        }

        @media (min-width: 768px) {
            .mobile-filters-toggle { display: none !important; }
            .mobile-filters-body,
            .mobile-filters-body.is-open {
                display: block !important;
            }
            .visible-phone-block { display: none !important; }
        }

        .loader {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #367FA9;
            width: 60px;
            height: 60px;
            -webkit-animation: spin 1s linear infinite;
            /* Safari */
            animation: spin 1s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

    </style>





    {{--<!-- jQuery 3 -->--}}
    <script src="{{ asset('dashboard_files/js/jquery.min.js') }}"></script>

    {{--noty--}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/plugins/noty/noty.css') }}">
    <script src="{{ asset('dashboard_files/plugins/noty/noty.min.js') }}"></script>

    {{--morris--}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/plugins/morris/morris.css') }}">

    {{--<!-- iCheck -->--}}
    <link rel="stylesheet" href="{{ asset('dashboard_files/plugins/icheck/all.css') }}">

    {{--html in  ie--}}
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    @stack('styles')

</head>
<body class="hold-transition skin-blue sidebar-mini">

    <div class="wrapper">

        <header class="main-header">

            {{--<!-- Logo -->--}}
            <a href="{{ asset($setting->logo) }}" class="logo">
                {{--<!-- mini logo for sidebar mini 50x50 pixels -->--}}
                <span class="logo-mini">A<b>T</b>B</span>
                <span class="logo-lg"><b>{{ $setting->name ?? '' }}</b></span>
            </a>

            <nav class="navbar navbar-static-top">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    {{-- <span class="sr-only">Toggle navigation</span> --}}
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">

                        <!-- Messages: style can be found in dropdown.less-->
                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-success">4</span>
                            </a>
                            <ul class="dropdown-menu">
                                {{-- <li class="header">You have 4 messages</li> --}}
                                {{-- <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">
                                        <li>
                                            <!-- start message -->
                                            <a href="#">
                                                <div class="pull-left">
                                                    {{-- <img src="{{ asset('dashboard_files/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image"> --}
                                                </div>
                                                <h4>
                                                    Support Team
                                                    <small>
                                                        <i class="fa fa-clock-o"></i> 5 mins
                                                    </small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li> --}}
                                <li class="footer">
                                    {{-- <a href="#">See All Messages</a> --}}
                                </li>
                            </ul>
                        </li>

                        {{--<!-- Notifications: style can be found in dropdown.less -->--}}
                        @php $headerAlertsTotal = (int) ($collectionAlertsCount ?? 0) + (int) ($stockAlertsCount ?? 0); @endphp
                        <li class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-bell-o"></i>
                                @if($headerAlertsTotal > 0)
                                    <span class="label label-warning" id="header-alerts-badge">{{ $headerAlertsTotal }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu" style="width:320px;">
                                <li class="header">
                                    تنبيهات المخزون
                                    @if(($stockAlertsCount ?? 0) > 0)
                                        ({{ $stockAlertsCount }})
                                    @endif
                                </li>
                                <li>
                                    <ul class="menu" id="stock-alerts-menu">
                                        @forelse($stockAlerts ?? [] as $stockAlert)
                                            <li>
                                                <a href="{{ $stockAlert['url'] }}">
                                                    <i class="fa fa-cube text-{{ ($stockAlert['level'] ?? '') === 'out' ? 'red' : 'yellow' }}"></i>
                                                    {{ $stockAlert['name'] }}
                                                    <br>
                                                    <small>
                                                        {{ $stockAlert['level_label'] }} —
                                                        المتاح: {{ $stockAlert['stock_display'] }}
                                                    </small>
                                                </a>
                                            </li>
                                        @empty
                                            <li><a href="#"><small class="text-muted">لا توجد منتجات منخفضة المخزون</small></a></li>
                                        @endforelse
                                    </ul>
                                </li>
                                <li class="header" style="border-top:1px solid #f4f4f4;">
                                    تنبيهات السداد
                                    @if(($collectionAlertsCount ?? 0) > 0)
                                        ({{ $collectionAlertsCount }})
                                    @endif
                                </li>
                                <li>
                                    <ul class="menu">
                                        @forelse($collectionAlerts ?? [] as $alert)
                                            @php
                                                $alertSchedule = app(\App\Services\CollectionScheduleService::class);
                                                $alertStatus = $alert['status'] ?? 'due_soon';
                                                $alertOrder = $alert['order'];
                                                $alertInst = $alert['installment'];
                                            @endphp
                                            <li>
                                                <a href="{{ route('dashboard.payments.index', ['client_id' => $alertOrder->client_id, 'order_id' => $alertOrder->id]) }}">
                                                    <i class="fa fa-warning text-{{ $alertStatus === 'overdue' ? 'red' : 'yellow' }}"></i>
                                                    {{ $alertOrder->client->name }} — {{ $alertOrder->order_number }}
                                                    <br>
                                                    <small>
                                                        قسط {{ number_format($alertInst->amount, 2) }} ج.س —
                                                        {{ $alertSchedule->scheduleStatusLabel($alertStatus) }}
                                                        ({{ $alertInst->due_at->format('d/m/Y') }})
                                                    </small>
                                                </a>
                                            </li>
                                        @empty
                                            <li><a href="#"><small class="text-muted">لا توجد تنبيهات سداد حالياً</small></a></li>
                                        @endforelse
                                    </ul>
                                </li>
                                <li class="footer">
                                    <a href="{{ route('dashboard.products.index') }}">المنتجات</a>
                                    ·
                                    <a href="{{ route('dashboard.collection-schedules.index', ['schedule_status' => 'alert']) }}">السداد</a>
                                </li>
                            </ul>
                        </li>

                        {{--<!-- Tasks: style can be found in dropdown.less -->--}}
                        <li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-flag-o"></i></a>
                            <ul class="dropdown-menu">
                                <li>
                                    {{--<!-- inner menu: contains the actual data -->--}}
                                    <ul class="menu">
                                        {{-- @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                        <li>
                                            <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                        {{ $properties['native'] }}
                                        </a>
                                </li>
                                @endforeach --}}
                            </ul>
                        </li>
                    </ul>
                    </li>

                    {{--<!-- User Account: style can be found in dropdown.less -->--}}
                    <li class="dropdown user user-menu">

                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset($setting->logo) }}" class="user-image" alt="User Image">
                             {{-- <img src="{{ asset('dashboard_files/img/' . $setting->logo) }}" alt="POS Image"> --}}
                            <span class="hidden-xs">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                        </a>
                        <ul class="dropdown-menu">

                            {{--<!-- User image -->--}}
                            <li class="user-header">
                                <img src="{{ asset($setting->logo) }}" class="img-circle" alt="User Image">

                                <p>
                                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                    {{-- <small>Member since 2 days</small> --}}
                                </p>
                            </li>

                            {{--<!-- Menu Footer-->--}}
                            <li class="user-footer">


                                <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">تسجيل خروج</a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>

                            </li>
                        </ul>
                    </li>
                    </ul>
                </div>
            </nav>

        </header>

        @include('dashboard.partials.collection-due-banner')

        @include('layouts.dashboard._aside')

        @yield('content')

        @include('partials._session')
        {{-- @include('partials._errors') --}}

        @include('dashboard.ai._styles')
        @include('dashboard.ai._widget')

        <footer class="main-footer">

            <strong>جميع الحقوق محفوظة  &copy;
                <a href="{{ $setting->facebook }}">{{ $setting->name ?? '' }}</a></strong>
          {{ date('Y') }}
        </footer>

    </div><!-- end of wrapper -->

    {{--<!-- Bootstrap 3.3.7 -->--}}
    <script src="{{ asset('dashboard_files/js/bootstrap.min.js') }}"></script>

    {{--icheck--}}
    <script src="{{ asset('dashboard_files/plugins/icheck/icheck.min.js') }}"></script>

    {{--<!-- FastClick -->--}}
    <script src="{{ asset('dashboard_files/js/fastclick.js') }}"></script>

    {{--<!-- AdminLTE App -->--}}
    <script src="{{ asset('dashboard_files/js/adminlte.min.js') }}"></script>

    {{--ckeditor standard--}}
    <script src="{{ asset('dashboard_files/plugins/ckeditor/ckeditor.js') }}"></script>

    {{--jquery number--}}
    <script src="{{ asset('dashboard_files/js/jquery.number.min.js') }}"></script>

    {{--print this--}}
    <script src="{{ asset('dashboard_files/js/printThis.js') }}"></script>

    {{--morris --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="{{ asset('dashboard_files/plugins/morris/morris.min.js') }}"></script>

    {{--custom js--}}
    <script src="{{ asset('dashboard_files/js/custom/image_preview.js') }}"></script>
    <script src="{{ asset('dashboard_files/js/custom/order.js') }}"></script>

    <script>
        $(document).ready(function() {

            $('.sidebar-menu').tree();

            //icheck
            $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                checkboxClass: 'icheckbox_minimal-blue'
                , radioClass: 'iradio_minimal-blue'
            });

            //delete
            $('.delete').click(function(e) {

                var that = $(this)

                e.preventDefault();

                var n = new Noty({
                    text: "حذف"
                    , type: "warning"
                    , killer: true
                    , buttons: [
                        Noty.button("عرض", 'btn btn-success mr-2', function() {
                            that.closest('form').submit();
                        }),

                        Noty.button("لايوجد ", 'btn btn-primary mr-2', function() {
                            n.close();
                        })
                    ]
                });

                n.show();

            }); //end of delete

            // // image preview
            // $(".image").change(function () {
            //
            //     if (this.files && this.files[0]) {
            //         var reader = new FileReader();
            //
            //         reader.onload = function (e) {
            //             $('.image-preview').attr('src', e.target.result);
            //         }
            //
            //         reader.readAsDataURL(this.files[0]);
            //     }
            //
            // });

            // CKEDITOR.config.language = "{{ app()->getLocale() }}";

            // تسمية أعمدة الجداول تلقائياً للجوال
            window.labelMobileTables = function labelMobileTables() {
                $('.box-body table.table, .dataTables_wrapper table.table, .panel-body table.table').each(function () {
                    var $table = $(this);
                    var labels = [];
                    var $heads = $table.find('thead th');
                    if (!$heads.length) {
                        $heads = $table.find('tr').first().children('th');
                    }
                    $heads.each(function () {
                        labels.push($.trim($(this).text()));
                    });
                    if (!labels.length) {
                        return;
                    }
                    $table.find('tbody tr, tr').each(function () {
                        if ($(this).children('th').length) {
                            return;
                        }
                        $(this).children('td').each(function (i) {
                            if (!$(this).attr('data-label') && labels[i]) {
                                $(this).attr('data-label', labels[i]);
                            }
                        });
                    });
                    // إخفاء صف العناوين داخل اللوحات إن لم يكن thead
                    $table.find('tr').first().children('th').closest('tr').addClass('mobile-hide-head');
                    if (!$table.parent().hasClass('table-responsive') && !$table.parent().hasClass('mobile-card-table')) {
                        $table.wrap('<div class="table-responsive mobile-card-table"></div>');
                    } else {
                        $table.parent().addClass('mobile-card-table');
                    }
                });
            };
            window.labelMobileTables();

            // طي فلاتر البحث على الجوال لتوفير المساحة
            function setupMobileFilters() {
                if (window.innerWidth > 767) {
                    return;
                }
                $('.products-toolbar, .orders-toolbar, .client-history-filters, .mobile-filter-panel').each(function () {
                    var $panel = $(this);
                    if ($panel.data('mobile-filter-ready') || $panel.closest('.mobile-filters-body').length) {
                        return;
                    }
                    var controlCount = $panel.find('input:not([type="hidden"]), select, textarea').length;
                    if (controlCount < 2) {
                        return;
                    }
                    $panel.data('mobile-filter-ready', 1);
                    var $wrap = $('<div class="mobile-filters-wrap"></div>');
                    var $btn = $('<button type="button" class="btn mobile-filters-toggle"><i class="fa fa-filter"></i> بحث وتصفية <i class="fa fa-chevron-down caret-icon"></i></button>');
                    var $body = $('<div class="mobile-filters-body"></div>');
                    var hasActive = false;
                    $panel.find('input:not([type="hidden"]), select').each(function () {
                        var v = $(this).val();
                        if (v && String(v).length) {
                            hasActive = true;
                        }
                    });
                    $panel.before($wrap);
                    $wrap.append($btn).append($body);
                    $body.append($panel);
                    if (hasActive) {
                        $body.addClass('is-open');
                        $btn.addClass('is-open');
                    }
                    $btn.on('click', function () {
                        $body.toggleClass('is-open');
                        $btn.toggleClass('is-open');
                    });
                });
            }
            setupMobileFilters();
            $(window).on('resize', function () {
                if (window.innerWidth > 767) {
                    $('.mobile-filters-body').addClass('is-open');
                }
            });

        }); //end of ready

    </script>
    @livewireScripts
    @stack('scripts')
    @include('dashboard.ai._script')
    @include('dashboard.stock._alerts_script')

</body>
</html>
