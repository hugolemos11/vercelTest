<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Address;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'username',
        'phone_number',
        'address_id',
        'permission',
        'nif',
        'holder',
        'iban',
        'enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the addresses associated with the user.
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the formulas for the user.
     */
    public function formulas()
    {
        return $this->hasMany(Formula::class);
    }
}
