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

            /* رأس وشريط جانبي — درج جوال */
            .main-header.phone-header {
                position: fixed !important;
                top: 0;
                right: 0;
                left: 0;
                width: 100% !important;
                height: 56px !important;
                min-height: 56px !important;
                z-index: 1040;
                display: flex !important;
                align-items: stretch;
                background: linear-gradient(135deg, #1e5f8a 0%, #3c8dbc 55%, #367fa9 100%) !important;
                box-shadow: 0 2px 10px rgba(15, 23, 42, .18);
                border: 0 !important;
            }
            .main-header.phone-header .logo {
                display: none !important;
            }
            .main-header.phone-header .navbar {
                flex: 1 1 auto;
                width: 100% !important;
                margin: 0 !important;
                min-height: 56px !important;
                background: transparent !important;
                display: flex !important;
                align-items: center;
                float: none !important;
                position: relative;
                padding: 0 6px 0 4px;
                gap: 4px;
            }
            .main-header.phone-header .fx-rate-chip {
                flex: 0 1 auto;
                margin: 0 2px;
                max-width: 38vw;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .main-header.phone-header .sidebar-toggle {
                float: none !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                padding: 0 !important;
                margin: 0 2px 0 0 !important;
                border-radius: 10px;
                background: rgba(255,255,255,.12) !important;
                color: #fff !important;
            }
            .main-header.phone-header .sidebar-toggle:hover,
            .main-header.phone-header .sidebar-toggle:focus {
                background: rgba(255,255,255,.2) !important;
                color: #fff !important;
            }
            .main-header.phone-header .sidebar-toggle .icon-bar {
                display: block;
                width: 18px;
                height: 2px;
                background: #fff;
                border-radius: 2px;
                margin: 3px auto;
            }
            .phone-header-brand {
                display: flex !important;
                align-items: center;
                flex: 1 1 auto;
                min-width: 0;
                padding: 0 8px;
                color: #fff !important;
                text-decoration: none !important;
            }
            .phone-header-brand-text {
                display: block;
                font-size: 15px;
                font-weight: 700;
                line-height: 1.25;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                color: #fff;
            }
            .main-header.phone-header .navbar-custom-menu {
                float: none !important;
                margin: 0 !important;
                flex: 0 0 auto;
            }
            .main-header.phone-header .navbar-custom-menu > .nav {
                display: flex;
                align-items: center;
                gap: 2px;
                margin: 0;
            }
            .main-header.phone-header .navbar-custom-menu > .nav > li {
                float: none !important;
            }
            .main-header.phone-header .navbar-custom-menu > .nav > li > a {
                padding: 0 !important;
                background: transparent !important;
                color: #fff !important;
            }
            .phone-header-icon,
            .phone-header-user {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                width: 44px !important;
                height: 44px !important;
                border-radius: 10px !important;
                position: relative;
                background: rgba(255,255,255,.1) !important;
            }
            .phone-header-icon > i {
                font-size: 18px;
            }
            .phone-header-badge {
                position: absolute !important;
                top: 4px !important;
                left: 4px !important;
                right: auto !important;
                min-width: 18px;
                height: 18px;
                line-height: 18px !important;
                padding: 0 5px !important;
                border-radius: 999px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                background: #f59e0b !important;
                color: #fff !important;
                text-align: center;
            }
            .phone-header-avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background: rgba(255,255,255,.22);
                color: #fff;
                font-size: 14px;
            }
            .phone-header-avatar-lg {
                width: 64px;
                height: 64px;
                font-size: 26px;
                margin: 12px auto 8px;
                background: rgba(255,255,255,.2);
            }
            .phone-header-dropdown {
                width: min(320px, 92vw) !important;
                max-width: 92vw;
                right: auto !important;
                left: 8px !important;
                border-radius: 12px !important;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(15,23,42,.2) !important;
            }
            .main-header.phone-header .user-menu > .dropdown-menu {
                left: 8px !important;
                right: auto !important;
                width: min(260px, 90vw);
                border-radius: 12px;
            }
            .main-header.phone-header .user-header {
                background: linear-gradient(135deg, #1e5f8a, #3c8dbc) !important;
                height: auto !important;
                padding: 16px 12px !important;
            }
            .main-header.phone-header .user-header img {
                display: none;
            }

            .content-wrapper,
            .right-side,
            .main-footer {
                margin-right: 0 !important;
                margin-left: 0 !important;
                transform: none !important;
            }
            .content-wrapper {
                padding-top: 56px !important;
                padding-bottom: 96px;
            }

            .main-sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                right: 0 !important;
                left: auto !important;
                width: min(82vw, 300px) !important;
                height: 100% !important;
                max-height: 100vh !important;
                padding-top: 56px !important;
                z-index: 1050 !important;
                overflow: hidden !important;
                box-shadow: -6px 0 24px rgba(0,0,0,.28);
                -webkit-transform: translate3d(105%, 0, 0) !important;
                transform: translate3d(105%, 0, 0) !important;
                -webkit-transition: -webkit-transform .22s ease !important;
                transition: transform .22s ease !important;
            }
            body.sidebar-open .main-sidebar,
            body.sidebar-open .main-sidebar.sidebar-open {
                -webkit-transform: translate3d(0, 0, 0) !important;
                transform: translate3d(0, 0, 0) !important;
            }
            body.sidebar-open .content-wrapper,
            body.sidebar-open .main-footer,
            body.sidebar-open .right-side {
                -webkit-transform: none !important;
                -ms-transform: none !important;
                -o-transform: none !important;
                transform: none !important;
                margin-right: 0 !important;
                margin-left: 0 !important;
            }

            .main-sidebar .sidebar {
                height: calc(100vh - 56px) !important;
                max-height: calc(100vh - 56px) !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
                padding-bottom: 28px;
            }
            .main-sidebar .user-panel {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 14px 14px 12px !important;
                border-bottom: 1px solid rgba(255,255,255,.08);
                min-height: auto;
                overflow: visible;
            }
            .main-sidebar .phone-user-avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(255,255,255,.12);
                color: #fff;
                font-size: 16px;
            }
            .main-sidebar .user-panel .pull-left.image {
                float: none !important;
                padding: 0 !important;
            }
            .main-sidebar .user-panel .info {
                position: static !important;
                left: auto !important;
                padding: 0 !important;
                font-size: 14px;
                line-height: 1.35;
                float: none !important;
            }
            .main-sidebar .user-panel .info > p {
                margin: 0 0 2px;
                font-weight: 700;
                color: #fff;
            }
            .sidebar-menu {
                padding-bottom: 40px !important;
            }
            .sidebar-menu > li > a {
                min-height: 48px;
                display: flex !important;
                align-items: center;
                gap: 10px;
                font-size: 15px !important;
                padding: 12px 16px !important;
                border-radius: 0;
            }
            .sidebar-menu > li > a > .fa {
                font-size: 16px;
                width: 22px;
                text-align: center;
                flex-shrink: 0;
            }
            .sidebar-menu > li > a > span {
                flex: 1;
                line-height: 1.3;
            }
            .sidebar-menu > li > a > .pull-right-container {
                margin: 0 !important;
                position: static !important;
            }
            .sidebar-menu .treeview-menu > li > a {
                min-height: 42px;
                display: flex !important;
                align-items: center;
                gap: 8px;
                padding: 10px 18px 10px 12px !important;
                font-size: 14px !important;
            }
            .sidebar-menu .treeview-menu > li > a > .fa {
                width: 16px;
                text-align: center;
            }

            /* طبقة تعتيم خلف السايدبار */
            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .45);
                z-index: 1045;
                -webkit-tap-highlight-color: transparent;
            }
            body.sidebar-open .sidebar-backdrop {
                display: block;
            }
            body.sidebar-open {
                overflow: hidden;
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

            /* أزرار الإجراءات المشتركة */
            .phone-action-bar {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                width: 100%;
            }
            .phone-action-bar .btn,
            .phone-action-bar .delete-form,
            .phone-action-bar form {
                width: 100%;
                margin: 0 !important;
            }
            .phone-action-bar .delete-form .btn,
            .phone-action-bar form .btn {
                width: 100%;
            }
            .phone-action-bar .btn {
                min-height: 46px !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                gap: 6px;
                font-weight: 700 !important;
            }
            .clients-action-bar,
            .purchase-action-bar {
                background: transparent;
                border: 0;
                padding: 0;
            }

            /* ترقيم الصفحات واضح على الهاتف */
            .products-pagination,
            .box-body .pagination,
            nav[role="navigation"] {
                margin-top: 14px;
            }
            .pagination {
                display: flex !important;
                flex-wrap: wrap;
                justify-content: center;
                gap: 6px;
                padding-right: 0;
            }
            .pagination > li {
                display: inline-block;
                float: none !important;
            }
            .pagination > li > a,
            .pagination > li > span,
            .page-link {
                min-width: 42px !important;
                min-height: 42px !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                border-radius: 8px !important;
                font-size: 14px !important;
                margin: 0 !important;
                padding: 8px 12px !important;
            }
        }

        @media (min-width: 768px) {
            .mobile-filters-toggle { display: none !important; }
            .mobile-filters-body,
            .mobile-filters-body.is-open {
                display: block !important;
            }
            .visible-phone-block { display: none !important; }
            .phone-header-brand { display: none !important; }
            .phone-header-avatar {
                display: none;
            }
            .main-header.phone-header .user-menu .user-image {
                display: inline-block;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                margin-top: -3px;
            }
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

        @include('layouts.dashboard._header')

        @include('dashboard.partials.collection-due-banner')

        @include('layouts.dashboard._aside')
        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

        @yield('content')

        @include('partials._session')
        {{-- @include('partials._errors') --}}

        @include('dashboard.ai._styles')
        @include('dashboard.exchange._styles')
        @include('dashboard.ai._widget')
        @include('dashboard.exchange._widget')

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

            // سايدبار الجوال: إغلاق بالضغط خارجاً / بعد اختيار رابط
            function closeMobileSidebar() {
                $('body').removeClass('sidebar-open').trigger('collapsed.pushMenu');
            }
            $(document).on('click', '#sidebar-backdrop', function () {
                closeMobileSidebar();
            });
            $(document).on('click', '.main-sidebar .sidebar-menu > li > a', function () {
                if (window.innerWidth > 767) {
                    return;
                }
                // اترك القوائم الفرعية مفتوحة؛ أغلق عند الرابط الحقيقي فقط
                if ($(this).attr('href') && $(this).attr('href') !== '#') {
                    setTimeout(closeMobileSidebar, 120);
                }
            });
            $(document).on('click', '.main-sidebar .treeview-menu a', function () {
                if (window.innerWidth <= 767) {
                    setTimeout(closeMobileSidebar, 120);
                }
            });
            $(document).on('click', '#ai-assistant-menu-link', function () {
                if (window.innerWidth <= 767) {
                    setTimeout(closeMobileSidebar, 80);
                }
            });

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
    @include('dashboard.exchange._script')
    @include('dashboard.stock._alerts_script')

</body>
</html>
