<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ControlUser extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'u_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'u_fullname',
        'u_loginname',
        'u_email',
        'u_password',
        'u_languages',
        'u_photo',
        'u_group',
        'u_cat',
        'u_tel',
        'u_city',
        'u_address',
        'u_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'u_password',
    ];

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->u_password;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'u_id';
    }
}
