@extends('layouts.dashboard.app')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>العملاء</h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
                <li><a href="{{ route('dashboard.clients.index') }}"> العملاء</a></li>
                <li class="active">إضافة</li>
            </ol>
        </section>

        <section class="content">

            <div class="box box-primary">

                <div class="box-header">
                    <h3 class="box-title">إضافة عميل جديد</h3>
                </div><!-- end of box header -->
                <div class="box-body">

                    @include('partials._errors')

                    <form action="{{ route('dashboard.clients.store') }}" method="post" enctype="multipart/form-data">

                        {{ csrf_field() }}
                        {{ method_field('post') }}

                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                       @for ($i = 0; $i < 2; $i++)
                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="text" name="phone[]" class="form-control" value="{{ old('phone.' . $i) }}">
                            </div>
                       @endfor

                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                        </div>

                        <div class="opening-box">
                            <label class="opening-toggle">
                                <input type="checkbox" id="has-opening" {{ old('opening_amount') ? 'checked' : '' }}>
                                لديه حساب قديم خارج النظام
                            </label>
                            <p class="text-muted" style="font-size:12px;margin:6px 0 0;">يُسجَّل مرة واحدة عند إضافة العميل، ويظهر في رصيده والفواتير غير المسددة وجدولة السداد والتقارير.</p>
                            <div id="opening-fields" style="{{ old('opening_amount') ? '' : 'display:none;' }}">
                                <div class="form-group">
                                    <label>إجمالي الحساب القديم (ج.س)</label>
                                    <input type="number" name="opening_amount" class="form-control" min="0" step="0.01" inputmode="decimal" value="{{ old('opening_amount') }}" placeholder="المبلغ المطلوب منه">
                                </div>
                                <div class="form-group">
                                    <label>مدفوع منه سابقاً (اختياري)</label>
                                    <input type="number" name="opening_paid" class="form-control" min="0" step="0.01" inputmode="decimal" value="{{ old('opening_paid', 0) }}">
                                </div>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>تاريخ الفاتورة القديمة</label>
                                            <input type="date" name="opening_date" class="form-control" value="{{ old('opening_date', now()->toDateString()) }}">
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>رقم الفاتورة القديمة</label>
                                            <input type="text" name="opening_reference" class="form-control" value="{{ old('opening_reference') }}" placeholder="اختياري">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>بيان الحساب</label>
                                    <textarea name="opening_details" class="form-control" rows="3" placeholder="الأصناف أو تفاصيل الدين كما في الفاتورة القديمة">{{ old('opening_details') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>صورة الفاتورة القديمة</label>
                                    <input type="file" name="opening_photo" class="form-control" accept="image/*">
                                    <small class="text-muted">من الهاتف يمكن التصوير أو اختيار صورة محفوظة.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</button>
                        </div>

                    </form><!-- end of form -->

                </div><!-- end of box body -->

            </div><!-- end of box -->

        </section><!-- end of content -->

    </div><!-- end of content wrapper -->

<style>
.opening-box {
    border: 1px solid #fcd34d;
    background: #fffbeb;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 14px;
}
.opening-toggle { font-weight: 700; margin: 0; }
.opening-box .form-control { min-height: 44px; }
</style>
<script>
document.getElementById('has-opening')?.addEventListener('change', function () {
    var box = document.getElementById('opening-fields');
    if (!box) return;
    box.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        var amount = box.querySelector('[name="opening_amount"]');
        if (amount) amount.value = '';
    }
});
</script>
@endsection
