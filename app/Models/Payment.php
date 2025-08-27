<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * اسم الجدول في قاعدة البيانات
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * الحقول القابلة للتعبئة
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'notes',
    ];

    /**
     * العلاقة مع الطلبات
     * كل دفعة تتبع طلب
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
