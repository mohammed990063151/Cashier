<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class DatabaseBackupController extends Controller
{
    protected $backupPath = 'backups/'; // مجلد التخزين في storage/app

    /**
     * صفحة عرض النسخ الاحتياطية
     */
    public function index()
    {
        // التأكد من وجود المجلد
        if (!Storage::exists($this->backupPath)) {
            Storage::makeDirectory($this->backupPath);
        }

        // جلب كل ملفات النسخ الاحتياطي
        $files = Storage::files($this->backupPath);

        $backups = collect($files)->map(function ($file) {
            return [
                'name'       => basename($file),
                'size'       => Storage::size($file),
                  'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file)),
            ];
        })->sortByDesc('created_at');

        return view('backup', compact('backups'));
    }

    /**
     * إنشاء نسخة احتياطية جديدة
     */
    public function backup()
    {
        $dbName = env('DB_DATABASE' ,'ataib'); // اسم قاعدة البيانات
        $dbUser = env('DB_USERNAME' , 'root'); // المستخدم
        $dbPass = env('DB_PASSWORD' ,''); // كلمة المرور (فارغة)
        $dbHost = env('DB_HOST', '127.0.0.1');

        $fileName = "backup-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
        $filePath = Storage::path($this->backupPath . $fileName);

        // إنشاء المجلد إذا لم يكن موجود
        if (!Storage::exists($this->backupPath)) {
            Storage::makeDirectory($this->backupPath);
        }

        // أمر النسخ الاحتياطي
        $command = $dbPass
            ? "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > {$filePath}"
            : "mysqldump --user={$dbUser} --host={$dbHost} {$dbName} > {$filePath}";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return redirect()->back()->with('error', '⚠️ فشل إنشاء النسخة الاحتياطية!');
        }

        return redirect()->back()->with('success', '✅ تم إنشاء النسخة الاحتياطية بنجاح!');
    }

    /**
     * تنزيل النسخة الاحتياطية
     */
    public function download($fileName)
    {
        $filePath = Storage::path($this->backupPath . $fileName);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', '⚠️ الملف غير موجود!');
        }

        return response()->download($filePath);
    }

    /**
     * حذف النسخة الاحتياطية
     */
    public function delete($fileName)
    {
        $filePath = $this->backupPath . $fileName;

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return redirect()->back()->with('success', '✅ تم حذف النسخة الاحتياطية بنجاح!');
        }

        return redirect()->back()->with('error', '⚠️ الملف غير موجود!');
    }
}
