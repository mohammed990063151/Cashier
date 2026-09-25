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
    ];

    protected $casts = [
        'usd_rate' => 'float',
        'show_usd' => 'boolean',
    ];
}
