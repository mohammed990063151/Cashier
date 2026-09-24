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

        /* جداول متجاوبة للجوال */
        @media (max-width: 767px) {
            .content-wrapper {
                padding-bottom: 80px;
                margin-left: 0 !important;
            }
            .main-header .logo {
                width: 100%;
            }
            .content {
                padding: 10px;
            }
            .box {
                border-radius: 8px;
            }
            .box-header .btn {
                margin-bottom: 6px;
            }
            .table-responsive,
            .mobile-card-table {
                border: 0;
                overflow-x: visible !important;
            }
            .mobile-card-table table,
            .box-body .table-responsive > table.table,
            .box-body > table.table {
                display: block;
                width: 100%;
            }
            .mobile-card-table thead,
            .box-body .table-responsive > table.table thead,
            .box-body > table.table thead {
                display: none;
            }
            .mobile-card-table tbody,
            .mobile-card-table tr,
            .mobile-card-table td,
            .box-body .table-responsive > table.table tbody,
            .box-body .table-responsive > table.table tr,
            .box-body .table-responsive > table.table td,
            .box-body > table.table tbody,
            .box-body > table.table tr,
            .box-body > table.table td {
                display: block;
                width: 100% !important;
                text-align: right !important;
            }
            .mobile-card-table tr,
            .box-body .table-responsive > table.table > tbody > tr,
            .box-body > table.table > tbody > tr {
                margin-bottom: 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background: #fff;
                padding: 8px 10px;
                box-shadow: 0 1px 2px rgba(0,0,0,.04);
            }
            .mobile-card-table td,
            .box-body .table-responsive > table.table > tbody > tr > td,
            .box-body > table.table > tbody > tr > td {
                border: none !important;
                border-bottom: 1px solid #f0f0f0 !important;
                padding: 8px 4px !important;
                position: relative;
                white-space: normal !important;
                word-break: break-word;
            }
            .mobile-card-table td:last-child,
            .box-body .table-responsive > table.table > tbody > tr > td:last-child,
            .box-body > table.table > tbody > tr > td:last-child {
                border-bottom: none !important;
            }
            .mobile-card-table td:before,
            .box-body .table-responsive > table.table > tbody > tr > td[data-label]:before,
            .box-body > table.table > tbody > tr > td[data-label]:before {
                content: attr(data-label);
                font-weight: 700;
                display: block;
                color: #666;
                margin-bottom: 2px;
                font-size: 12px;
            }
            .mobile-card-table td .btn,
            .box-body .table .btn {
                display: inline-block;
                margin: 3px 2px;
            }
            .mobile-card-table img,
            .box-body .table img {
                max-width: 72px !important;
                height: auto !important;
            }
            .client-history-filters .form-group {
                margin-bottom: 10px;
            }
            .small-box h3 {
                font-size: 22px;
            }
            .box-header .box-title {
                font-size: 16px;
                display: block;
                float: none !important;
            }
            #ai-assistant-toggle.ai-fab {
                bottom: 16px;
                left: 12px;
            }
            .breadcrumb {
                display: none;
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
            function labelMobileTables() {
                $('.box-body table.table').each(function () {
                    var $table = $(this);
                    var labels = [];
                    $table.find('thead th').each(function () {
                        labels.push($.trim($(this).text()));
                    });
                    if (!labels.length) {
                        return;
                    }
                    $table.find('tbody tr').each(function () {
                        $(this).children('td').each(function (i) {
                            if (!$(this).attr('data-label') && labels[i]) {
                                $(this).attr('data-label', labels[i]);
                            }
                        });
                    });
                    if (!$table.parent().hasClass('table-responsive') && !$table.parent().hasClass('mobile-card-table')) {
                        $table.wrap('<div class="table-responsive mobile-card-table"></div>');
                    } else {
                        $table.parent().addClass('mobile-card-table');
                    }
                });
            }
            labelMobileTables();

        }); //end of ready

    </script>
    @livewireScripts
    @stack('scripts')
    @include('dashboard.ai._script')
    @include('dashboard.stock._alerts_script')

</body>
</html>
