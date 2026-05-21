@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>نسبة أرباح المنتجات</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <form method="get" class="form-inline">
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                    <input type="date" name="to" class="form-control" value="{{ $to }}" style="margin:0 8px;">
                    <button type="submit" class="btn btn-primary">تصفية</button>
                </form>
            </div>
            <div class="alert alert-info">
                <strong>هامش الربح %</strong> = (الربح ÷ إجمالي المبيعات) × 100 لكل منتج.
            </div>
            <div class="box-body table-responsive">
                <table id="productRatioTable" class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>المبيعات</th>
                            <th>التكلفة</th>
                            <th>الربح</th>
                            <th>هامش %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productProfits as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['product'] }}</td>
                            <td>{{ number_format($item['sales'], 2) }}</td>
                            <td>{{ number_format($item['cost'], 2) }}</td>
                            <td>{{ number_format($item['profit'], 2) }}</td>
                            <td>{{ $item['margin_percent'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <canvas id="productRatioChart" height="120" style="max-height:280px;margin-top:20px;"></canvas>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $('#productRatioTable').DataTable({ order: [[5, 'desc']], pageLength: 50, language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' } });
    new Chart(document.getElementById('productRatioChart'), {
        type: 'bar',
        data: {
            labels: @json(array_column($productProfits, 'product')),
            datasets: [{
                label: 'هامش الربح %',
                data: @json(array_column($productProfits, 'margin_percent')),
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
