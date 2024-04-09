<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Address extends Model
{

    protected $table = 'addresses';

    protected $fillable = [
        'street',
        'city',
        'country',
        'postal_code',
        'enabled',
    ];

    /**
     * Get the user record associated with the address.
     */


}
