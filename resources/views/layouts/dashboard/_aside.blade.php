<aside class="main-sidebar">
    <section class="sidebar">

        <div class="user-panel">
            <div class="pull-left image">
                {{-- <img src="{{ asset('dashboard_files/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image"> --}}
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>
                <a href="#"><i class="fa fa-circle text-success"></i> متصل</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-th"></i><span>لوحة التحكم</span></a></li>
            @if (auth()->user()->hasPermission('read_categories'))
            <li><a href="{{ route('dashboard.categories.index') }}"><i class="fa fa-th"></i> <span>التصنيفات</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_products'))
            <li><a href="{{ route('dashboard.products.index') }}"><i class="fa fa-th"></i> <span>المنتجات</span></a></li>
            @endif
            <li>
                <a href="{{ route('dashboard.suppliers.index') }}">
                    <i class="fa fa-truck"></i>
                    <span>الموردين</span>
                </a>
            </li>
            @if (auth()->user()->hasPermission('read_clients'))
            <li><a href="{{ route('dashboard.clients.index') }}"><i class="fa fa-th"></i> <span>العملاء</span></a></li>
            @endif

            @if (auth()->user()->hasPermission('read_orders'))
            <li><a href="{{ route('dashboard.orders.index') }}"><i class="fa fa-th"></i> <span>الطلبات</span></a></li>

            <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-cubes"></i> <span>اداراة الطلبات</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('dashboard.orders.index') }}"><i class="fa fa-circle-o"></i> الطلبات </a></li>
                    <li><a href="{{ route('dashboard.payments.index') }}"><i class="fa fa-circle-o"></i> المدفوعات </a></li>
                    <li>
                        <a href="{{ route('dashboard.purchase-invoices.index') }}">
                            <i class="fa fa-file-text"></i>
                            <span>فواتير الشراء</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('dashboard.sale-invoices.index') }}">
                            <i class="fa fa-file-text"></i>
                            <span>فواتير البيع</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('dashboard.orders.trashed') }}">
                            <i class="fa fa-trash"></i> <span>الطلبات المحذوفة</span>
                        </a>
                    </li>




                </ul>
            </li>
            @endif

            @if (auth()->user()->hasPermission('read_users'))
            <li><a href="{{ route('dashboard.users.index') }}"><i class="fa fa-th"></i> <span>المستخدمين</span></a></li>
            @endif

            <li>
                <a href="{{ route('dashboard.expenses.index') }}"><i class="fa fa-money"></i> <span>المصروفات</span></a>
            </li>

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




            @if (auth()->user()->hasPermission('read_stock'))
            <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-cubes"></i> <span>المخزون</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('stock.in') }}"><i class="fa fa-circle-o"></i> إدخال للمخزون</a></li>
                    <li><a href="{{ route('stock.out') }}"><i class="fa fa-circle-o"></i> إخراج من المخزون</a></li>
                    <li><a href="{{ route('stock.report') }}"><i class="fa fa-circle-o"></i> تقرير المخزون</a></li>
                </ul>
            </li>
            @endif

            <li class="treeview {{ request()->routeIs('dashboard.reports.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-line-chart"></i> <span>التقارير</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">


                    <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                        <a href="#">
                            <i class="fa fa-cubes"></i> <span>  المبيعات</span>
                                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="{{ route('dashboard.reports.sales') }}"><i class="fa fa-circle-o"></i> تقرير المبيعات</a></li>
                            <li><a href="{{ route('dashboard.reports.summary') }}" class="fa fa-circle-o">
                                    📊 تقرير مجمل
                                </a></li>
                            <li>
                                <a href="{{ route('dashboard.reports.detailed') }}" class="fa fa-circle-o">
                                    📑 تقرير مفصل
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('dashboard.reports.byCategory') }}" class="fa fa-circle-o">
                                    🏷️ تقرير حسب التصنيف
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('dashboard.reports.slas.unpaid') }}" class="fa fa-circle-o">
                                    💳 الفواتير غير المسددة
                                </a>
                            </li>




                        </ul>


                    </li>



                      <li class="treeview {{ request()->routeIs('stock.*') ? 'active menu-open' : '' }}">
                        <a href="#">
                            <i class="fa fa-cubes"></i> <span>   الأرباح والخسائر</span>
                                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                          <li><a href="{{ route('dashboard.reports.profit') }}"><i class="fa fa-circle-o"></i> تقرير الأرباح والخسائر</a></li>
                             <li><a href="{{ route('dashboard.reports.profit_detailed') }}"><i class="fa fa-line-chart"></i> أرباح مفصل</a></li>

    <li><a href="{{ route('dashboard.reports.profit_summary') }}"><i class="fa fa-pie-chart"></i> أرباح مجمل</a></li>
    <li><a href="{{ route('dashboard.reports.profit_ratio') }}"><i class="fa fa-percent"></i> نسبة أرباح المنتجات</a></li>







                        </ul>


                    </li>
                     <li>
            <a href="{{ route('dashboard.reports.reports.index') }}">
                <i class="fa fa-circle-o"></i> 👥 قائمة العملاء مع الأرصدة
            </a>
        </li>
          <li>

            <a href="{{ route('dashboard.reports.suppliers.index') }}">
                <i class="fa fa-circle-o"></i> كشف حساب الموردين
            </a>
        </li>
        <li>

            <a href="{{ route('dashboard.reports.purchases.index') }}">
                <i class="fa fa-circle-o"></i> المشتريات
            </a>
        </li>

           <li>

            <a href="{{ route('dashboard.reports.inventory.report') }}">
                <i class="fa fa-circle-o"></i> المخزن
            </a>
        </li>
  <li>

            <a href="{{ route('dashboard.reports.reports.expenses') }}">
                <i class="fa fa-circle-o"></i> تقرير المصروفات
            </a>
        </li>

         <li>

            <a href="{{ route('dashboard.reports.report.cash') }}">
                <i class="fa fa-circle-o"></i> تقرير الخزينة
            </a>
        </li>
                    {{-- <li class="treeview {{ request()->routeIs('reports.clients.*') ? 'active menu-open' : '' }}">
    <a href="#">
        <i class="fa fa-users"></i> <span>تقارير العملاء</span>
        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
    </a>
    <ul class="treeview-menu">
        <li><a href="{{ route('dashboard.reports.clients') }}"><i class="fa fa-circle-o"></i> تقرير العملاء</a></li>
        <li>
            <a href="{{ route('dashboard.reports.reports.index') }}">
                <i class="fa fa-circle-o"></i> 👥 قائمة العملاء مع الأرصدة
            </a>
        </li>
        <li>
            <a href="{{ route('dashboard.reports.reports.invoices', ['client' => 1]) }}">
                <i class="fa fa-circle-o"></i> 🧾 فواتير عميل محدد
            </a>
        </li>
        <li>
            <a href="{{ route('dashboard.reports.reports.products', ['client' => 1]) }}">
                <i class="fa fa-circle-o"></i> 📦 المنتجات المباعة لعميل
            </a>
        </li>
        <li>
            <a href="{{ route('dashboard.reports.reports.statement', ['client' => 1]) }}">
                <i class="fa fa-circle-o"></i> 📑 كشف حساب عميل
            </a>
        </li>
    </ul>
</li> --}}


                </ul>
            </li>
        </ul>
    </section>
</aside>
