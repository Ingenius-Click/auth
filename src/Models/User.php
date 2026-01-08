<?php

namespace Ingenius\Auth\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Ingenius\Auth\Traits\MustVerifyEmailForTenant;
use Ingenius\Core\Interfaces\HasCustomerProfile;
use Ingenius\Core\Traits\HasCustomerProfileTrait;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasCustomerProfile, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, HasCustomerProfileTrait, MustVerifyEmailForTenant;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $table = 'users';
}
