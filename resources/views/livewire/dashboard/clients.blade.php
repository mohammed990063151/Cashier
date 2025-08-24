<div class="content-wrapper">

    <section class="content-header">
        <h1>العملاء</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">العملاء</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary">

            <div class="box-header with-border">
                <h3 class="box-title" style="margin-bottom: 15px">العملاء <small>{{ $clients->total() }}</small></h3>

                {{-- بحث مباشر --}}
                <div class="row">
                    <div class="col-md-4">
                        {{-- <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="بحث بالاسم أو الهاتف أو العنوان"> --}}
                        <input type="text" id="searchInput" wire:model.debounce.500ms="search" placeholder="بحث في كل الأعمدة" class="form-control">
                    </div>



                    <div class="col-md-4">

                        @if (auth()->user()->hasPermission('create_clients'))
                        <a href="{{ route('dashboard.clients.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> اضافة عميل</a>
                        @else
                        <a href="#" class="btn btn-primary disabled"><i class="fa fa-plus"></i> اضافة عميل</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="box-body">



                {{-- جدول العملاء --}}
                @if($clients->count() > 0)
                <table id="productsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>العنوان</th>
                            <th>إضافة طلب</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $index => $client)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $client->name }}</td>
                            <td>{{ is_array($client->phone) ? implode('-', $client->phone) : $client->phone }}</td>
                            <td>{{ $client->address }}</td>
                            <td>
                                @if(auth()->user()->hasPermission('create_orders'))
                                <a href="{{ route('dashboard.clients.orders.create', $client->id) }}" class="btn btn-primary btn-sm">إضافة طلب</a>
                                @else
                                <a href="#" class="btn btn-primary btn-sm disabled">إضافة طلب</a>
                                @endif
                            </td>
                            <td>
                                @if (auth()->user()->hasPermission('update_clients'))
                                <a href="{{ route('dashboard.clients.edit', $client->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i>تعديل</a>
                                @else
                                <a href="#" class="btn btn-info btn-sm disabled"><i class="fa fa-edit"></i>تعديل</a>
                                @endif
                                @if (auth()->user()->hasPermission('delete_clients'))
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $client->id }})">
                                    <i class="fa fa-trash"></i> حذف
                                </button>
                                1
                                @else
                                <button class="btn btn-danger btn-sm disabled"><i class="fa fa-trash"></i>حذف</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- ترقيم الصفحات --}}
                {{ $clients->links() }}

                @else
                <h4>لا توجد بيانات</h4>
                @endif

            </div>

        </div>

    </section>

</div>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmDelete(clientId) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذفه!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // إنشاء form ديناميكي وإرساله
                let form = document.createElement('form');
                form.action = '/dashboard/clients/' + clientId; // رابط الحذف
                form.method = 'POST';

                let csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                let methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        })
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const table = document.getElementById('productsTable');
        const rows = table.querySelectorAll('tbody tr');
        input.addEventListener('keyup', function() {
            const filter = input.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });

</script>

@endpush
