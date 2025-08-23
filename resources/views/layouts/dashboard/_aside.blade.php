<aside class="main-sidebar">

    <section class="sidebar">

        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('dashboard_files/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li><a href="{{ route('dashboard.reports.overview') }}"><i class="fa fa-th"></i><span>@lang('site.dashboard')</span></a></li>
            {{-- <li class="{{ request()->routeIs('dashboard.reports.*') ? 'mm-active' : '' }}">
                <a class="ai-icon d-flex justify-content-between align-items-center" href="{{ route('dashboard.reports.overview') }}">
                    <span>
                        <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 0h14V7H7v2zm0 4h14v-2H7v2zm0 4h14v-2H7v2z" />
                        </svg>
                        <span class="ml-2">التقارير</span>
                    </span>
                </a>
            </li> --}}
            @if (auth()->user()->hasPermission('read_categories'))
            <li><a href="{{ route('dashboard.categories.index') }}"><i class="fa fa-th"></i><span>@lang('site.categories')</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_products'))
            <li><a href="{{ route('dashboard.products.index') }}"><i class="fa fa-th"></i><span>@lang('site.products')</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_clients'))
            <li><a href="{{ route('dashboard.clients.index') }}"><i class="fa fa-th"></i><span>@lang('site.clients')</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_orders'))
            <li><a href="{{ route('dashboard.orders.index') }}"><i class="fa fa-th"></i><span>@lang('site.orders')</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_users'))
            <li><a href="{{ route('dashboard.users.index') }}"><i class="fa fa-th"></i><span>@lang('site.users')</span></a></li>
            @endif

            {{-- @if (auth()->user()->hasPermission('read_expenses')) --}}
            <li class="{{ request()->routeIs('expenses') ? 'active' : '' }}">
                <a href="{{ route('dashboard.expenses.index') }}"><i class="fa fa-money"></i><span>@lang('site.expenses')</span></a>
            </li>
            {{-- @endif --}}

            {{-- @if (auth()->user()->hasPermission('read_cash')) --}}
            <li class="treeview {{ request()->routeIs('cash.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-archive"></i>
                    <span>@lang('site.cash')</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    {{-- صفحة حركة الخزينة --}}
                    <li class="{{ request()->routeIs('cash.index') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.index') }}">
                            <i class="fa fa-circle-o"></i> @lang('site.cash_movements')
                        </a>
                    </li>

                    {{-- صفحة إعدادات الخزينة --}}
                    <li class="{{ request()->routeIs('cash.settings') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.settings') }}">
                            <i class="fa fa-circle-o"></i> @lang('site.cash_settings')
                        </a>
                    </li>
                </ul>
            </li>

            {{-- @endif --}}
            <li class="{{ request()->routeIs('purchase-invoices.*') ? 'mm-active' : '' }}">
                <a class="ai-icon d-flex justify-content-between align-items-center" href="{{ route('dashboard.purchase-invoices.create') }}">
                    <span>
                        <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M19,7H9V5H19M19,13H9V11H19M19,19H9V17H19M5,7H7V5H5M5,13H7V11H5M5,19H7V17H5V19Z" />
                        </svg>
                        <span class="ml-2">فواتير الشراء</span>
                    </span>
                </a>
            </li>
            <li class="{{ request()->routeIs('sale-invoices.*') ? 'mm-active' : '' }}">
                <a class="ai-icon d-flex justify-content-between align-items-center" href="{{ route('dashboard.sale_invoices.index') }}">
                    <span>
                        <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M19,7H9V5H19M19,13H9V11H19M19,19H9V17H19M5,7H7V5H5M5,13H7V11H5M5,19H7V17H5V19Z" />
                        </svg>
                        <span class="ml-2">فواتير البيع</span>
                    </span>
                </a>
            </li>
            <li class="{{ request()->routeIs('suppliers.*') ? 'mm-active' : '' }}">
                <a class="ai-icon d-flex justify-content-between align-items-center" href="{{ route('dashboard.suppliers.index') }}">
                    <span>
                        <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M12,2A10,10 0 1,0 22,12A10,10 0 0,0 12,2M12,5A2,2 0 0,1 14,7A2,2 0 0,1 12,9A2,2 0 0,1 10,7A2,2 0 0,1 12,5M12,11A5,5 0 0,1 17,16H7A5,5 0 0,1 12,11Z" />
                        </svg>
                        <span class="ml-2">الموردين</span>
                    </span>
                </a>
            </li>


            @if (auth()->user()->hasPermission('read_stock'))
            <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-cubes"></i> <span>@lang('site.stock')</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('stock.in') ? 'active' : '' }}">
                        <a href="{{ route('stock.in') }}"><i class="fa fa-circle-o"></i>@lang('site.stock_in')</a>
                    </li>
                    <li class="{{ request()->routeIs('stock.out') ? 'active' : '' }}">
                        <a href="{{ route('stock.out') }}"><i class="fa fa-circle-o"></i>@lang('site.stock_out')</a>
                    </li>
                    <li class="{{ request()->routeIs('stock.report') ? 'active' : '' }}">
                        <a href="{{ route('stock.report') }}"><i class="fa fa-circle-o"></i>@lang('site.stock_report')</a>
                    </li>
                </ul>
            </li>
            @endif

            <li class="treeview {{ request()->routeIs('reports.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-line-chart"></i> <span>@lang('site.reports')</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.reports.sales') }}"><i class="fa fa-circle-o"></i>@lang('site.sales_report')</a>
                    </li>
                    <li class="{{ request()->routeIs('dashboard.reports.profit') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.reports.profit') }}"><i class="fa fa-circle-o"></i>@lang('site.profit_loss')</a>
                    </li>
                    <li class="{{ request()->routeIs('dashboard.reports.clients') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.reports.clients') }}"><i class="fa fa-circle-o"></i>@lang('site.clients_report')</a>
                    </li>
                </ul>
            </li>

            @if (auth()->user()->hasPermission('read_users'))
            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                {{-- <a href="{{ route('users.index') }}"><i class="fa fa-user"></i><span>@lang('site.users')</span></a> --}}
            </li>
            @endif
            {{--</li> --}}
        </ul>
        <!-- resources/views/layouts/dashboard/sidebar.blade.php -->
        <ul class="metismenu" id="menu">
            <!-- ... عناصر القائمة الأخرى ... -->

            <li class="{{ request()->routeIs('dashboard.reports.*') ? 'mm-active' : '' }}">
                <a class="ai-icon d-flex justify-content-between align-items-center" href="#">
                    <span>
                        <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 0h14V7H7v2zm0 4h14v-2H7v2zm0 4h14v-2H7v2z" />
                        </svg>
                        <span class="ml-2">التقارير</span>
                    </span>
                    <i class="fa fa-angle-left"></i>
                </a>
                <ul class="mm-collapse">
                    <!-- المبيعات -->
                    {{-- <li><a href="{{ route('dashboard.reports.sales.detail') }}">تقرير مبيعات مفصل</a>
            </li> --}}
            <li><a href="{{ route('dashboard.reports.sales.summary') }}">تقرير مبيعات مجمل</a></li>
            <li><a href="{{ route('dashboard.reports.sales.by_category') }}">تقرير مبيعات حسب التصنيف</a></li>
            <li><a href="{{ route('dashboard.reports.sales.unpaid_invoices') }}">فواتير مبيعات غير مسددة</a></li>
            <li><a href="{{ route('dashboard.reports.sales.all_invoices') }}">جميع فواتير المبيعات</a></li>

            <!-- الأرباح -->
            {{-- <li><a href="{{ route('dashboard.reports.profits.detail') }}">تقرير أرباح مفصل</a></li> --}}
            <li><a href="{{ route('dashboard.reports.profits.summary') }}">تقرير أرباح مجمل</a></li>
            <li><a href="{{ route('dashboard.reports.profits.by_products') }}">نسبة أرباح المنتجات</a></li>

            <!-- العملاء -->
            <li><a href="{{ route('dashboard.reports.clients.remaining') }}">المبالغ المتبقية عند العملاء</a></li>
            <li><a href="{{ route('dashboard.reports.clients.invoices') }}">فواتير عميل</a></li>
            <li><a href="{{ route('dashboard.reports.clients.products') }}">المنتجات المباعة لعميل</a></li>
            <li><a href="{{ route('dashboard.reports.clients.statement') }}">كشف حساب العميل</a></li>

            <!-- الموردين -->
            {{-- <li><a href="{{ route('dashboard.reports.suppliers.remaining') }}">المبالغ المتبقية للموردين</a></li> --}}
            <li><a href="{{ route('dashboard.reports.suppliers.invoices') }}">فواتير مورد</a></li>
            <li><a href="{{ route('dashboard.reports.suppliers.products') }}">المنتجات المشتراه من مورد</a></li>
            <li><a href="{{ route('dashboard.reports.suppliers.statement') }}">كشف حساب المورد</a></li>

            <!-- المشتريات -->
            {{-- <li><a href="{{ route('dashboard.reports.purchases.detail') }}">تقرير مشتريات مفصل</a></li> --}}
            <li><a href="{{ route('dashboard.reports.purchases.summary') }}">تقرير مشتريات مجمل</a></li>
            <li><a href="{{ route('dashboard.reports.purchases.by_category') }}">تقرير مشتريات حسب التصنيف</a></li>
            <li><a href="{{ route('dashboard.reports.purchases.unpaid_invoices') }}">فواتير مشتريات غير مسددة</a></li>
            <li><a href="{{ route('dashboard.reports.purchases.all_invoices') }}">جميع فواتير المشتريات</a></li>

            <!-- المخزن -->
            <li><a href="{{ route('dashboard.reports.stock.detail') }}">جرد المخزن مفصل</a></li>
            <li><a href="{{ route('dashboard.reports.stock.summary') }}">جرد المخزن مجمل</a></li>
            <li><a href="{{ route('dashboard.reports.stock.price_changes') }}">حركة تغير أسعار الشراء</a></li>

            <!-- المصروفات -->
            {{-- <li><a href="{{ route('dashboard.reports.expenses.detail') }}">تقرير مصروفات مفصل</a></li> --}}
            <li><a href="{{ route('dashboard.reports.expenses.summary') }}">تقرير مصروفات مجمل</a></li>

            <!-- الخزينة -->
            <li><a href="{{ route('dashboard.reports.cash') }}">حركة الخزينة</a></li>
        </ul>
        </li>
        </ul>


    </section>

</aside>
