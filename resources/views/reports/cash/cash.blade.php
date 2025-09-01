@extends('layouts.dashboard.app')
@section('title','تقرير حركة الخزينة')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>💰 تقرير حركة الخزينة</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">

                {{-- فلتر تاريخ --}}
                <form method="GET" class="form-inline mb-3">
                    <div class="form-group mr-2">
                        <label>من:</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>
                    <div class="form-group mr-2">
                        <label>إلى:</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">تصفية</button>
                </form>

                {{-- جدول الحركات --}}
                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاريخ الحركة</th>
                            <th>الوصف</th>
                            <th>إضافة مبلغ</th>
                            <th>سحب مبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalAdded = 0;
                            $totalDeducted = 0;
                        @endphp
                        @foreach($transactions as $index => $t)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->transaction_date)->format('m/d/Y') }}</td>
                            <td>{{ $t->description ?? '-' }}</td>
                            <td>
                                @if($t->type === 'add')
                                    {{ number_format($t->amount,2) }}
                                    @php $totalAdded += $t->amount; @endphp
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($t->type === 'deduct')
                                    {{ number_format($t->amount,2) }}
                                    @php $totalDeducted += $t->amount; @endphp
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">الإجمالي الحالي:</th>
                            <th>{{ number_format($totalAdded,2) }}</th>
                            <th>{{ number_format($totalDeducted,2) }}</th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

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
$(document).ready(function() {
    $('.datatable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','pdf','print'],
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"},
        "pageLength": 10,
        "ordering": true
    });
});
</script>
@endpush
