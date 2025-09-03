<div class="content-wrapper">

    <section class="content-header">
        <h1>العملاء</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">العملاء</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary shadow-sm">
            <div class="box-header with-border d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h3 class="box-title">العملاء <small class="text-muted">{{ $clients->total() }}</small></h3>
               
                <div class="d-flex flex-wrap gap-2">
                     <br /> <br />
                    <input type="text" id="searchInput" wire:model.debounce.500ms="search" class="form-control" placeholder="بحث في كل الأعمدة">
                    <br /><br />
                    @if(auth()->user()->hasPermission('create_clients'))
                        <a href="{{ route('dashboard.clients.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة عميل</a>
                    @else
                        <a href="#" class="btn btn-primary disabled"><i class="fa fa-plus"></i> إضافة عميل</a>
                    @endif
                </div>
            </div>

            <div class="box-body table-responsive">
                @if($clients->count() > 0)
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="thead-dark">
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
                                            <a href="{{ route('dashboard.clients.orders.create', $client->id) }}" class="btn btn-success btn-sm">إضافة طلب</a>
                                        @else
                                            <a href="#" class="btn btn-success btn-sm disabled">إضافة طلب</a>
                                        @endif
                                    </td>
                                    <td class="d-flex gap-1 flex-wrap">
                                        @if(auth()->user()->hasPermission('update_clients'))
                                            <a href="{{ route('dashboard.clients.edit', $client->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> تعديل</a>
                                        @else
                                            <a href="#" class="btn btn-info btn-sm disabled"><i class="fa fa-edit"></i> تعديل</a>
                                        @endif

                                        @if(auth()->user()->hasPermission('delete_clients'))
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $client->id }})"><i class="fa fa-trash"></i> حذف</button>
                                        @else
                                            <button class="btn btn-danger btn-sm disabled"><i class="fa fa-trash"></i> حذف</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $clients->links() }}
                    </div>
                @else
                    <h4 class="text-center text-muted mt-3">لا توجد بيانات</h4>
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
                let form = document.createElement('form');
                form.action = '/dashboard/clients/' + clientId;
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        })
    }

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const table = document.getElementById('productsTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            input.addEventListener('keyup', function() {
                const filter = input.value.toLowerCase();
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush
