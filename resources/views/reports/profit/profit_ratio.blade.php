@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>نسبة أرباح المنتجات  المبيعات</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body table-responsive">
                <table id="productRatioTable" class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>الربح</th>
                            <th>نسبة الربح %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productProfits as $index => $item)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{ $item['product'] }}</td>
                            <td>{{ number_format($item['profit'],2) }} ج.س</td>
                            <td>{{ $item['ratio'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 text-center">
        <canvas id="productRatioChart" height="250"></canvas>
    </div>
</div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- JS DataTables -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
$(document).ready(function(){
    $('#productRatioTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','csv','pdf','print'],
        order: [[3,'desc']],
        pageLength: 50,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('productRatioChart').getContext('2d');
    const productRatioChart = new Chart(ctx, {
        type: 'bar', // يمكن تغييره إلى 'pie' إذا أحببت
        data: {
            labels: @json(array_column($productProfits, 'product')),
            datasets: [{
                label: 'نسبة الربح %',
                data: @json(array_column($productProfits, 'ratio')),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // يسمح بتعديل الحجم
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'نسبة الربح %'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'المنتج'
                    }
                }
            }
        }
    });
</script>

@endpush
