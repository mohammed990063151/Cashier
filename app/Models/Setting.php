<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'logo', 'email', 'phone', 'address',
        'facebook', 'twitter', 'instagram', 'linkedin',
        'usd_rate', 'show_usd',
        'whatsapp_enabled', 'whatsapp_base_url', 'whatsapp_username',
        'whatsapp_password', 'whatsapp_device_id', 'whatsapp_staff_phone',
    ];

    protected $casts = [
        'usd_rate' => 'float',
        'show_usd' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];
}
