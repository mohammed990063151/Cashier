<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laratrust\Traits\HasRolesAndPermissions;
class User extends Authenticatable
{
    use
        HasRolesAndPermissions,
        Notifiable;


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'image'
    ];

    protected $appends = ['image_path'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);

    }//end of get first name

    public function getLastNameAttribute($value)
    {
        return ucfirst($value);

    }//end of get last name

    public function getImagePathAttribute()
    {
        return asset('uploads/user_images/' . $this->image);

    }//end of get image path

    // علاقة User مع Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    // علاقة User مع Permission
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id');
    }

}//end of model
