<div class="content-wrapper">

    <section class="content-header mb-3">
        <h1>الصندوق :
            <span class="d-inline-flex align-items-center px-3 py-1 text-white font-weight-bold" style="background:#28a745; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-size:1em;">
                <i class="fa fa-money-bill-wave mr-1"></i>
                {{ number_format($cash->balance, 2) }}
            </span>
        </h1>
    </section>
    <section class="content">

        {{-- رسائل الحالة --}}
        @if(session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">

            <!-- جدول الحركات -->
            <div class="col-md-8">
                <div class="box box-primary" style="box-shadow:0 2px 8px #e0e0e0;">
                    <div class="box-header with-border" style="background:#f8f9fa;"><br />
                        <h3 class="box-title"><i class="fa fa-list-alt text-primary"></i> حركات الصندوق</h3>
                        <br /><br />
                        <div class="row mt-2 mb-3">
                            <div class="col-md-6">
                                <input type="number" wire:model.lazy="filterAmount" class="form-control" placeholder="ابحث عن مبلغ">
                            </div>
                            <div class="col-md-6">
                                <input type="text" wire:model.lazy="filterDescription" class="form-control" placeholder="ابحث في الوصف">
                            </div>
                            <br /> <br /> <br />
                            <div class="col-md-6">
                                <input type="date" wire:model.lazy="filterDate" class="form-control" placeholder="بحث بتاريخ">
                            </div>
                            <div class="col-md-6">
                                <select wire:model.lazy="filterCategory" class="form-control">
                                    <option value="all">جميع الحركات</option>
                                    <option value="order">فواتير المبيعات</option>
                                    <option value="returns">فواتير المرتجعات</option>
                                    <option value="purchases">فواتير المشتريات</option>
                                    <option value="clients">سندات العملاء</option>
                                    <option value="suppliers">سندات الموردين</option>
                                    <option value="operational">المصروفات</option>
                                    <option value="direct">إضافة/سحب نقد مباشر</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive">
                        @if($transactions->count() > 0)
                        <table class="table table-hover table-bordered" style="background:#fff;">
                            <thead style="background:#e9ecef;">
                                <tr>
                                    <th>تاريخ الحركة</th>
                                    <th>الوصف</th>
                                    <th class="text-success">إضافة مبلغ</th>
                                    <th class="text-danger">سحب مبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $trx)
                                <tr>
                                    <td>{{ $trx->transaction_date }}</td>
                                    <td>{{ $trx->description }}</td>
                                    <td class="text-success">{{ $trx->type == 'add' ? number_format($trx->amount, 2) : '-' }}</td>
                                    <td class="text-danger">{{ $trx->type == 'deduct' ? number_format($trx->amount, 2) : '-' }}</td>
                                </tr>
                                @endforeach
                                <tr style="background:#f1f3f5; font-weight:bold;">
                                    <td colspan="2" class="text-right">الإجمالي مضاف:</td>
                                    <td class="text-success">{{number_format($totalAdded, 2) }}</td></tr>
                                    
                                    <td colspan="2" class="text-right">الإجمالي الخصم:</td>
<td></td>
                                    <td class="text-danger">{{ number_format($totalDeducted, 2)  }}</td></tr>
                                <tr style="background:#f1f3f5; font-weight:bold;">
                                    
                                    <td colspan="2" class="text-right">الإجمالي الحالي:</td>
                                    <td class="text-success">{{ $totalAmount >= 0 ? number_format($totalAmount, 2) : '-' }}</td>
                                    <td class="text-danger">{{ $totalAmount < 0 ? number_format(abs($totalAmount), 2) : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-2">
                            {{ $transactions->links() }}
                        </div>
                        @else
                        <h4 class="text-center text-muted mt-3">لا توجد أي سجلات حتى الآن</h4>
                        @endif
                    </div>
                </div>
            </div>

            <!-- إضافة حركة -->
            <div class="col-md-4">
                <div class="box box-primary" style="box-shadow:0 2px 8px #e0e0e0;">
                    <div class="box-header with-border" style="background:#f8f9fa;">
                        <h3 class="box-title"><i class="fa fa-plus-square text-success"></i> إضافة/سحب مبلغ</h3>
                    </div>

                    <div class="box-body">
                        <form wire:submit.prevent="storeTransaction">
                            <div class="form-group">
                                <label>نوع الحركة</label>
                                <select wire:model="type" class="form-control" required>
                                    <option value="">اختر النوع</option>
                                    <option value="add">إضافة مبلغ</option>
                                    <option value="deduct">سحب مبلغ</option>
                                </select>
                                @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>المبلغ</label>
                                <input type="number" wire:model="amount" step="0.01" class="form-control" required>
                                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>الوصف</label>
                                <input type="text" wire:model="description" class="form-control">
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>تاريخ الحركة</label>
                                <input type="date" wire:model="transaction_date" class="form-control" required>
                                @error('transaction_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <input type="hidden" wire:model="category" value="direct">

                            <button type="submit" class="btn btn-success btn-block mt-2">حفظ</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </section>
</div>
