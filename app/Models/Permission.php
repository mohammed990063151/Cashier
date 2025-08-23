<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'display_name', 'description'];

    // علاقة Permission مع Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }

    // علاقة Permission مع User
    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user', 'permission_id', 'user_id');
    }
}
