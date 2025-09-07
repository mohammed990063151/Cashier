<aside class="main-sidebar">
    <section class="sidebar">

        {{-- لوحة المستخدم --}}
        <div class="user-panel">
            <div class="pull-left image">
                {{-- أيقونة أو صورة المستخدم --}}
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> متصل</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            {{-- لوحة التحكم --}}
            <li>
                <a href="{{ route('dashboard.welcome') }}">
                    <i class="fa fa-dashboard"></i> <span>لوحة التحكم</span>
                </a>
            </li>

            {{-- التصنيفات --}}
            @if (auth()->user()->hasPermission('read_categories'))
            <li>
                <a href="{{ route('dashboard.categories.index') }}">
                    <i class="fa fa-tags"></i> <span>التصنيفات</span>
                </a>
            </li>
            @endif

            {{-- المنتجات --}}
            @if (auth()->user()->hasPermission('read_products'))
            <li>
                <a href="{{ route('dashboard.products.index') }}">
                    <i class="fa fa-cube"></i> <span>المنتجات</span>
                </a>
            </li>
            @endif

            {{-- الموردين --}}
            <li>
                <a href="{{ route('dashboard.suppliers.index') }}">
                    <i class="fa fa-truck"></i> <span>الموردين</span>
                </a>
            </li>

            {{-- العملاء --}}
            @if (auth()->user()->hasPermission('read_clients'))
            <li>
                <a href="{{ route('dashboard.clients.index') }}">
                    <i class="fa fa-user"></i> <span>العملاء</span>
                </a>
            </li>
            @endif

            {{-- الطلبات --}}
            @if (auth()->user()->hasPermission('read_orders'))
            <li class="treeview {{ request()->routeIs('orders.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-shopping-cart"></i> <span>الطلبات</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.orders.index') }}"><i class="fa fa-circle-o"></i> الطلبات</a></li>
                    <li><a href="{{ route('dashboard.payments.index') }}"><i class="fa fa-circle-o"></i> المدفوعات</a></li>
                    <li><a href="{{ route('dashboard.purchase-invoices.index') }}"><i class="fa fa-file-text"></i> فواتير الشراء</a></li>
                    <li><a href="{{ route('dashboard.sale-invoices.index') }}"><i class="fa fa-file-text"></i> فواتير البيع</a></li>
                    <li><a href="{{ route('dashboard.orders.trashed') }}"><i class="fa fa-trash"></i> الطلبات المحذوفة</a></li>
                </ul>
            </li>
            @endif

            {{-- المستخدمون --}}
            @if (auth()->user()->hasPermission('read_users'))
            <li>
                <a href="{{ route('dashboard.users.index') }}">
                    <i class="fa fa-users"></i> <span>المستخدمون</span>
                </a>
            </li>
            @endif

            {{-- المصروفات --}}
            <li>
                <a href="{{ route('dashboard.expenses.index') }}">
                    <i class="fa fa-money"></i> <span>المصروفات</span>
                </a>
            </li>

            {{-- الخزينة --}}
            <li class="treeview {{ request()->routeIs('cash.*') ? 'active menu-open' : '' }}">
                <a href="#"><i class="fa fa-archive"></i> <span>الخزينة</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('cash.index') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.index') }}"><i class="fa fa-circle-o"></i> حركة الخزينة</a>
                    </li>
                    <li class="{{ request()->routeIs('cash.settings') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.cash.settings') }}"><i class="fa fa-circle-o"></i> إعدادات الخزينة</a>
                    </li>
                </ul>
            </li>

            {{-- المخزون --}}
            @if (auth()->user()->hasPermission('read_stock'))
            <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                <a href="#"><i class="fa fa-cubes"></i> <span>المخزون</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('stock.in') }}"><i class="fa fa-circle-o"></i> إدخال للمخزون</a></li>
                    <li><a href="{{ route('stock.out') }}"><i class="fa fa-circle-o"></i> إخراج من المخزون</a></li>
                    <li><a href="{{ route('stock.report') }}"><i class="fa fa-circle-o"></i> تقرير المخزون</a></li>
                </ul>
            </li>
            @endif

            {{-- التقارير --}}
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
            <li>
                <a href="{{ route('dashboard.settings.edit') }}">
                    <i class="fa fa-cogs"></i> <span>اعدادات</span>
                </a>
            </li>
            <li>
<a href="{{ route('dashboard.database.backup') }}" >
   <span> 📦 تحميل نسخة احتياطية </span>
</a>
            </li>
               <li>
    <a href="{{ route('dashboard.admin.trash') }}">
        <span> 🗑️ سلة المحذوفات</span>
    </a>
  </li>

        </ul>
    </section>
</aside>
