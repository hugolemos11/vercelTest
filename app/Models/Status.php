<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    protected $fillable = [
        'description',
        'enabled',
    ];

    /**
     * Get the formulas for the status.
     */
    public function formulas()
    {
        return $this->hasMany(Formula::class);
    }
}
