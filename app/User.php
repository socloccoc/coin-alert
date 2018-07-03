<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $casts = [ 'is_root_admin' => 'boolean','is_active' => 'boolean' ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'is_root_admin',
        'is_admin_approved',
        'is_active',
        'username',
        'name',
        'email',
        'password',
        'confirm_code',
        'device_identifier',
        'device_identifier_old_app',
        'type',
        'enable_ios',
        'token_password',
        'active_password',
        'expire_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'token_password', 'active_password', 'expire_at'
    ];

}
