@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>إدارة النسخ الاحتياطية</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">النسخ الاحتياطية</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">

            <div class="box-header with-border">
                <h3 class="box-title" style="margin-bottom: 15px">النسخ الاحتياطية <small>{{ $backups->count() }}</small></h3>

                <div class="mb-3">
                    <a href="{{ route('dashboard.backup.create') }}" class="btn btn-success">📦 إنشاء نسخة احتياطية جديدة</a>
                </div>
            </div><!-- end box-header -->

            <div class="box-body table-responsive">
                @if($backups->count() > 0)
                <table class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم النسخة</th>
                            <th>الحجم (KB)</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $index => $backup)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $backup['name'] }}</td>
                            <td>{{ round($backup['size']/1024, 2) }}</td>
                            <td>{{ $backup['created_at'] }}</td>
                            <td>
                                <a href="{{ route('dashboard.backup.download', $backup['name']) }}" class="btn btn-primary btn-sm">⬇️ تحميل</a>
                                <form action="{{ route('dashboard.backup.delete', $backup['name']) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('هل تريد الحذف؟')">🗑️ حذف</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <h3 class="text-center text-muted">لا توجد نسخ احتياطية حالياً</h3>
                @endif
            </div><!-- end box-body -->

        </div><!-- end box -->
    </section><!-- end content -->

</div><!-- end content-wrapper -->
@endsection
