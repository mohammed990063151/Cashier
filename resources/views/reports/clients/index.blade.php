@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>قائمة العملاء</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <form class="form-inline" method="get">
                    <div class="form-group">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="بحث باسم أو هاتف">
                    </div>
                    <button class="btn btn-primary">بحث</button>
                </form>
            </div>

            <div class="box-body table-responsive">
                <table id="clientsTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم العميل</th>
                            <th>هاتف</th>
                            <th>عدد الفواتير</th>
                            <th>إجمالي المتبقي</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->id }}</td>
                            <td>{{ $client->name }}</td>
                            <td>{{ is_array($client->phone) ? implode(' - ', $client->phone) : $client->phone }}</td>
                            <td>{{ $client->orders->count() }}</td>
                            <td>{{ number_format($client->remaining_balance,2) }} ج.س</td>
                            <td>
                                <a href="{{ route('dashboard.reports.reports.show', $client->id) }}" class="btn btn-sm btn-info">تفاصيل</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS/JS (احرص تكون موجودة في layout أو على الأقل هنا) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function(){
    $('#clientsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','csv','pdf','print'],
        order: [[4,'desc']],
        pageLength: 25,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
    });
});
</script>
@endpush
