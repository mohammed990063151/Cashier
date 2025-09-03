@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header mb-3 d-flex justify-content-between align-items-center flex-wrap">
        <h1>دفعات الطلب: #{{ $order->id }}</h1>
        <div class="mt-2 mt-md-0">
           
            <a href="{{ route('dashboard.payments.index') }}" class="btn btn-default mb-1">
                <i class="fa fa-arrow-left"></i> العودة للطلبات
            </a>
        </div>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">تفاصيل دفعات الطلب</h3>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>ملاحظات</th>
                            <th>تاريخ الدفع</th>
                            <th>حفظ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->payments as $index => $payment)
                        <tr>
                            <td class="align-middle text-center">{{ $index + 1 }}</td>
                            <td colspan="5">
                                <form action="{{ route('dashboard.payment.update', $payment->id) }}" method="POST" class="row gy-2 gx-3 align-items-center">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                        <input type="number" name="amount" step="0.01" class="form-control" value="{{ old('amount', $payment->amount) }}" required>
                                        @error('amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                        <select name="method" class="form-control" required>
                                            <option value="cash" {{ $payment->method == 'cash' ? 'selected' : '' }}>كاش</option>
                                            <option value="bank" {{ $payment->method == 'bank' ? 'selected' : '' }}>تحويل بنكي</option>
                                        </select>
                                        @error('method')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-3">
                                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $payment->notes) }}" placeholder="ملاحظات">
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-3">
                                        <input type="text" class="form-control" disabled value="{{ $payment->created_at->format('d-m-Y') }}">
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <button type="submit" class="btn btn-primary btn-block w-100">
                                            <i class="fa fa-save"></i> حفظ
                                        </button>
                                    </div>
                                </form>
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
