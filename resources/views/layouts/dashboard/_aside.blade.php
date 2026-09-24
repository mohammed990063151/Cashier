<aside class="main-sidebar">
    <section class="sidebar">

        <div class="user-panel">
            <div class="pull-left image"></div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> متصل</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li>
                <a href="{{ route('dashboard.welcome') }}">
                    <i class="fa fa-dashboard"></i> <span>لوحة التحكم</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" id="ai-assistant-menu-link">
                    <i class="fa fa-magic"></i> <span>المساعد الذكي</span>
                </a>
            </li>

            @if (auth()->user()->hasPermission('read_categories'))
            <li>
                <a href="{{ route('dashboard.categories.index') }}">
                    <i class="fa fa-tags"></i> <span>التصنيفات</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_products'))
            <li>
                <a href="{{ route('dashboard.products.index') }}">
                    <i class="fa fa-cube"></i> <span>المنتجات</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_suppliers') || auth()->user()->hasPermission('read_purchases'))
            <li class="treeview {{ request()->routeIs('dashboard.suppliers.*', 'dashboard.supplier-schedules.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-truck"></i> <span>الموردين</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @if (auth()->user()->hasPermission('read_suppliers'))
                    <li><a href="{{ route('dashboard.suppliers.index') }}"><i class="fa fa-circle-o"></i> قائمة الموردين</a></li>
                    <li><a href="{{ route('dashboard.supplier-schedules.index') }}"><i class="fa fa-calendar"></i> جدولة السداد</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_clients'))
            <li class="treeview {{ request()->routeIs('dashboard.clients.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-user"></i> <span>العملاء</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.clients.index') }}"><i class="fa fa-circle-o"></i> قائمة العملاء</a></li>
                    @if (\Illuminate\Support\Facades\Route::has('dashboard.clients.history'))
                    <li><a href="{{ route('dashboard.clients.history') }}"><i class="fa fa-history"></i> سجل معاملات العملاء</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_orders'))
            <li class="treeview {{ request()->routeIs('dashboard.orders.*', 'dashboard.payments.*', 'dashboard.collection-schedules.*', 'dashboard.purchase-invoices.*', 'dashboard.sale-invoices.*', 'dashboard.direct-sale*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-shopping-cart"></i> <span>الطلبات</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.orders.index') }}"><i class="fa fa-circle-o"></i> الطلبات</a></li>
                    <li><a href="{{ route('dashboard.direct-sale') }}"><i class="fa fa-bolt"></i> بيع مباشر</a></li>
                    <li><a href="{{ route('dashboard.payments.index') }}"><i class="fa fa-circle-o"></i> المدفوعات</a></li>
                    <li><a href="{{ route('dashboard.collection-schedules.index') }}"><i class="fa fa-calendar-check-o"></i> جدولة السداد</a></li>
                    @if (auth()->user()->hasPermission('read_purchases') || auth()->user()->hasPermission('read_orders'))
                    <li><a href="{{ route('dashboard.purchase-invoices.index') }}"><i class="fa fa-file-text"></i> فواتير الشراء</a></li>
                    @endif
                    <li><a href="{{ route('dashboard.sale-invoices.index') }}"><i class="fa fa-file-text"></i> فواتير البيع</a></li>
                    <li><a href="{{ route('dashboard.orders.trashed') }}"><i class="fa fa-trash"></i> الطلبات المحذوفة</a></li>
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_users'))
            <li>
                <a href="{{ route('dashboard.users.index') }}">
                    <i class="fa fa-users"></i> <span>المستخدمون</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_expenses'))
            <li>
                <a href="{{ route('dashboard.expenses.index') }}">
                    <i class="fa fa-money"></i> <span>المصروفات</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_cash'))
            <li class="treeview {{ request()->routeIs('dashboard.cash.*') ? 'active menu-open' : '' }}">
                <a href="#"><i class="fa fa-archive"></i> <span>الخزينة</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('dashboard.cash.index') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.index') }}"><i class="fa fa-circle-o"></i> حركة الخزينة</a>
                    </li>
                    <li class="{{ request()->routeIs('dashboard.cash.settings') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.settings') }}"><i class="fa fa-circle-o"></i> إعدادات الخزينة</a>
                    </li>
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_stock') || auth()->user()->hasPermission('read_reports'))
            <li class="treeview {{ request()->routeIs('dashboard.reports.inventory.*', 'dashboard.stock-alerts') ? 'active menu-open' : '' }}">
                <a href="#"><i class="fa fa-cubes"></i> <span>المخزون</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.reports.inventory.report') }}"><i class="fa fa-circle-o"></i> تقرير المخزون</a></li>
                    <li><a href="{{ route('dashboard.products.index') }}"><i class="fa fa-circle-o"></i> قائمة المنتجات</a></li>
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_reports'))
            <li class="treeview {{ request()->routeIs('dashboard.reports.*') ? 'active menu-open' : '' }}">
                <a href="#"><i class="fa fa-line-chart"></i> <span>التقارير</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.reports.sales') }}"><i class="fa fa-circle-o"></i> تقرير المبيعات</a></li>
                    <li><a href="{{ route('dashboard.reports.summary') }}"><i class="fa fa-circle-o"></i> تقرير مجمل</a></li>
                    <li><a href="{{ route('dashboard.reports.detailed') }}"><i class="fa fa-circle-o"></i> تقرير مفصل</a></li>
                    <li><a href="{{ route('dashboard.reports.byCategory') }}"><i class="fa fa-circle-o"></i> تقرير حسب التصنيف</a></li>
                    <li><a href="{{ route('dashboard.reports.slas.unpaid') }}"><i class="fa fa-circle-o"></i> الفواتير غير المسددة</a></li>
                    <li><a href="{{ route('dashboard.reports.profit') }}"><i class="fa fa-circle-o"></i> تقرير الأرباح والخسائر</a></li>
                    <li><a href="{{ route('dashboard.reports.profit_detailed') }}"><i class="fa fa-line-chart"></i> أرباح مفصل</a></li>
                    <li><a href="{{ route('dashboard.reports.profit_summary') }}"><i class="fa fa-pie-chart"></i> أرباح مجمل</a></li>
                    <li><a href="{{ route('dashboard.reports.profit_ratio') }}"><i class="fa fa-percent"></i> نسبة أرباح المنتجات</a></li>
                    <li><a href="{{ route('dashboard.reports.reports.index') }}"><i class="fa fa-users"></i> قائمة العملاء مع الأرصدة</a></li>
                    <li><a href="{{ route('dashboard.reports.suppliers.index') }}"><i class="fa fa-truck"></i> كشف حساب الموردين</a></li>
                    <li><a href="{{ route('dashboard.reports.purchases.index') }}"><i class="fa fa-circle-o"></i> المشتريات</a></li>
                    <li><a href="{{ route('dashboard.reports.inventory.report') }}"><i class="fa fa-cubes"></i> المخزن</a></li>
                    <li><a href="{{ route('dashboard.reports.reports.expenses') }}"><i class="fa fa-money"></i> تقرير المصروفات</a></li>
                    <li><a href="{{ route('dashboard.reports.report.cash') }}"><i class="fa fa-archive"></i> تقرير الخزينة</a></li>
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_settings') || auth()->user()->hasPermission('update_settings'))
            <li>
                <a href="{{ route('dashboard.settings.edit') }}">
                    <i class="fa fa-cogs"></i> <span>إعدادات</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_backup') || auth()->user()->hasPermission('create_backup'))
            <li>
                <a href="{{ route('dashboard.database.backup') }}">
                    <i class="fa fa-database"></i> <span>نسخة احتياطية</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_trash'))
            <li>
                <a href="{{ route('dashboard.admin.trash') }}">
                    <i class="fa fa-trash"></i> <span>سلة المحذوفات</span>
                </a>
            </li>
            @endif
        </ul>
    </section>
</aside>
