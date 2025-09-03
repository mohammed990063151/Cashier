<div class="content-wrapper">
    {{-- العنوان والمسار --}}
    <section class="content-header">
        <h1>الرئيسية</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">نظرة عامة</li>
        </ol>
    </section>

    {{-- مؤشرات تحميل --}}


    {{-- المحتوى الرئيسي --}}
    <section class="content">
        {{-- الفلاتر --}}
        {{-- <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label>اختيار نطاق زمني:</label>
                    <select class="form-control" wire:model="selectedRange">
                        <option value="">اختيار...</option>
                        <option value="today">اليوم</option>
                        <option value="week">هذا الأسبوع</option>
                        <option value="month">هذا الشهر</option>
                        <option value="custom">مخصص</option>
                    </select>
                </div>
            </div>
            @if($selectedRange === 'custom')
                <div class="col-md-3">
                    <label>من:</label>
                    <input type="date" wire:model="startDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>إلى:</label>
                    <input type="date" wire:model="endDate" class="form-control">
                </div>
            @endif
        </div> --}}

        {{-- البطاقات الرئيسية --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $categories_count }}</h3>
                        <p>التصنيفات</p>
                    </div>
                    <div class="icon"><i class="ion ion-bag"></i></div>
                    <a href="{{ route('dashboard.categories.index') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $products_count }}</h3>
                        <p>المنتجات</p>
                    </div>
                    <div class="icon"><i class="ion ion-stats-bars"></i></div>
                    <a href="{{ route('dashboard.products.index') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $clients_count }}</h3>
                        <p>العملاء</p>
                    </div>
                    <div class="icon"><i class="fa fa-user"></i></div>
                    <a href="{{ route('dashboard.clients.index') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $users_count }}</h3>
                        <p>المستخدمون</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="{{ route('dashboard.users.index') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- نظرة عامة مالية --}}
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
                        <h3>{{ number_format($clientsOverview['total_due'] ?? 0, 2) }} ج.س</h3>
                        <p>المبالغ المتبقية للعملاء</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ number_format($suppliersOverview['total_due'] ?? 0, 2)  }} ج.س</h3>
                        <p>المبالغ المتبقية للموردين</p>
                    </div>
                    <div class="icon"><i class="fa fa-truck"></i></div>
                </div>
            </div>
        </div>

        {{-- آخر الحركات في الخزينة --}}

        {{-- الرسوم البيانية --}}
        <div class="row mt-4">
            <div class="col-md-6">
    <div class="box box-primary">
        <div class="box-header"><h3 class="box-title">المبالغ المتبقية للعملاء</h3></div>
        <div class="box-body"><canvas id="clientsDueChart" height="300"></canvas></div>
    </div>
</div>

            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header"><h3 class="box-title">الأرباح والمصروفات</h3></div>
                    <div class="box-body"><canvas id="profitDoughnutChart" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="box box-warning">
                    <div class="box-header"><h3 class="box-title">خط زمني يومي للحركات المالية</h3></div>
                    <div class="box-body"><canvas id="dailyCashChart" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="box box-info">
                    <div class="box-header"><h3 class="box-title">أفضل المنتجات مبيعًا</h3></div>
                    <div class="box-body"><canvas id="topProductsChart" height="150"></canvas></div>
                </div>
            </div>
        </div>
           <div class="box box-info mt-4">
            <div class="box-header"><h3 class="box-title">آخر الحركات في الخزينة</h3></div>
            <div class="box-body">
                <ul class="list-group">
                    @foreach($transactions->take(5) as $transaction)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $transaction->transaction_date }} - {{ $transaction->type === 'add' ? 'إضافة' : 'خصم' }}
                            بقيمة <strong>{{ number_format($transaction->amount, 2) }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- أفضل المنتجات --}}
        <div class="table-responsive mt-4">
            <h4>أفضل المنتجات مبيعًا</h4>
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>المنتج</th>
                        <th>عدد الطلبات</th>
                        <th>السعر</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->orders_count }}</td>
                            <td>{{ number_format($product->sale_price, 2) }} ج.س</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
 <div wire:loading wire:target="startDate,endDate" class="alert alert-info">
        جاري تحميل البيانات...
    </div>

    {{-- نمو المبيعات --}}
    <div class="alert alert-success">
        <strong>نمو المبيعات:</strong>
        {{ number_format($salesGrowth, 2) }}%
        (مقارنة بالشهر السابق)
    </div>
</div>
    </section>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // بيانات المبيعات اليومية
    const ctxDaily = document.getElementById('dailyCashChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [
                { label: 'الإضافات', data: @json($dailyAdded), borderColor: 'green', fill: false, tension: 0.1 },
                { label: 'الخصومات', data: @json($dailyDeducted), borderColor: 'red', fill: false, tension: 0.1 },
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // المتبقي اليومي للعملاء بدون فلتر التاريخ
let clientsDueChart = new Chart(document.getElementById('clientsDueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($clientsDueChart['labels'] ?? []),
        datasets: [{
            label: 'المبالغ المتبقية للعملاء',
            data: @json($clientsDueChart['data'] ?? []),
            backgroundColor: 'rgba(255, 206, 86, 0.7)'
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});


    // أرباح ومصروفات
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

    // أفضل المنتجات
    const ctxTop = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctxTop, {
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
