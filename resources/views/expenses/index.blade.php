@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>المصروفات
            <small>{{ $expenses->total() }} مصروف</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المصروفات</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">المصروفات</h3>

                        <div class="row mb-3">
                            <!-- البحث وزر الإضافة في نفس الصف -->
                            <div class="col-md-6">
                                <input type="text" id="searchInput" class="form-control" placeholder="ابحث هنا...">
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('dashboard.expenses.create') }}" class="btn btn-primary">➕ إضافة مصروف</a>
                            </div>
                        </div>
                    </div><!-- end of box header -->

                    <div class="box-body table-responsive">

                        @if(session('success'))
                        <div class="alert alert-success text-center">{{ session('success') }}</div>
                        @endif

                     <table id="expensesTable" class="table table-hover table-bordered text-center table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th scope="col">#</th>
            <th scope="col">العنوان</th>
            <th scope="col">المبلغ</th>
            <th scope="col">النوع</th>
            <th scope="col">ملاحظة</th>
            <th scope="col">تاريخ الإنشاء</th>
            <th scope="col">الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $expense)
        <tr class="table-light">
            <td>{{ $expense->id }}</td>
            <td class="fw-bold">{{ $expense->title }}</td>
            <td class="text-danger fw-bold">{{ number_format($expense->amount, 2) }} ج.س</td>
            <td>
                <span class="badge {{ $expense->type == 'operational' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                    {{ $expense->type == 'operational' ? 'تشغيلي' : 'أخرى' }}
                </span>
            </td>
            <td>{{ $expense->note ?? '-' }}</td>
            <td>{{ $expense->created_at->format('d-m-Y') }}</td>
            <td>
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <a href="{{ route('dashboard.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning" title="تعديل">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <form action="{{ route('dashboard.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="حذف">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-muted">لا توجد سجلات</td>
        </tr>
        @endforelse
    </tbody>
</table>


                        <div class="d-flex justify-content-center mt-2">
                            {{ $expenses->links() }}
                        </div>

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content -->

</div><!-- end of content-wrapper -->

@push('scripts')
<script>
    // جلب عناصر البحث والجدول
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('expensesTable');
    const rows = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();

        // بدء من index 1 لتجاهل رأس الجدول
        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                let cell = cells[j];
                if (cell && cell.textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });

</script>



@endpush

@endsection
