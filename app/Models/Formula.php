<?php

namespace App\Models;

use App\Models\User;
use App\Models\Status;

use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{

    protected $table = 'formulas';

    protected $fillable = [
        'user_id',
        'status_id',
        'prescription',
        'prescriber',
        'patient',
        'recipe_url',
        'request_date',
        'enabled',
    ];

    /**
     * Get the user record associated with the formula.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    /**
     * Get the status record associated with the formula.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
