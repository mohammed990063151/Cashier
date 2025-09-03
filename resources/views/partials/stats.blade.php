<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $categories_count }}</h3>
                <p>التصنيفات</p>
            </div>
            <div class="icon"><i class="ion ion-bag"></i></div>
            <a href="{{ route('dashboard.categories.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $products_count }}</h3>
                <p>المنتجات</p>
            </div>
            <div class="icon"><i class="ion ion-stats-bars"></i></div>
            <a href="{{ route('dashboard.products.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $clients_count }}</h3>
                <p>العملاء</p>
            </div>
            <div class="icon"><i class="fa fa-user"></i></div>
            <a href="{{ route('dashboard.clients.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $users_count }}</h3>
                <p>المستخدمون</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
            <a href="{{ route('dashboard.users.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- الصف الثاني --}}
<div class="row mt-3">
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ number_format($salesOverview['total_sales'] ?? 0, 2) }} ج.س</h3>
                <p>إجمالي المبيعات</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ number_format($profitsOverview['total_profit'] ?? 0, 2) }} ج.س</h3>
                <p>إجمالي الأرباح</p>
            </div>
            <div class="icon"><i class="fa fa-dollar-sign"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $clientsOverview['total_due'] ?? 0 }} ج.س</h3>
                <p>المبالغ المتبقية للعملاء</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $suppliersOverview['total_due'] ?? 0 }} ج.س</h3>
                <p>المبالغ المتبقية للموردين</p>
            </div>
            <div class="icon"><i class="fa fa-truck"></i></div>
        </div>
    </div>
</div>

{{-- الصف الثالث --}}
<div class="row mt-3">
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ number_format($purchasesOverview['total_purchases'] ?? 0, 2) }} ج.س</h3>
                <p>إجمالي المشتريات</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-basket"></i></div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>{{ number_format($expensesOverview['total_expenses'] ?? 0, 2) }} ج.س</h3>
                <p>إجمالي المصروفات</p>
            </div>
            <div class="icon"><i class="fa fa-credit-card"></i></div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>{{ number_format($cashOverview['balance'] ?? 0, 2) }} ج.س</h3>
                <p>الرصيد الحالي بالخزينة</p>
            </div>
            <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
        </div>
    </div>
</div>






@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart for daily cash
    const ctxCash = document.getElementById('dailyCashChart').getContext('2d');
    new Chart(ctxCash, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [
                {
                    label: 'الإضافات',
                    data: @json($dailyAdded),
                    borderColor: 'green',
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'الخصومات',
                    data: @json($dailyDeducted),
                    borderColor: 'red',
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Profit and expenses doughnut chart
    const ctxProfit = document.getElementById('profitDoughnutChart').getContext('2d');
    new Chart(ctxProfit, {
        type: 'doughnut',
        data: {
            labels: ['الأرباح', 'المصروفات'],
            datasets: [{
                data: [{{ $profitsOverview['total_profit'] ?? 0 }}, {{ $expensesOverview['total_expenses'] ?? 0 }}],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: { responsive: true }
    });

    // Top products chart
    const ctxTopProducts = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctxTopProducts, {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('name')),
            datasets: [{
                label: 'عدد الطلبات',
                data: @json($topProducts->pluck('orders_count')),
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
