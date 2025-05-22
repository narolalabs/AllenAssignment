<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'borough',
        'house_number',
        'street_name',
        'violation_type',
        'description',
        'violation_number'
    ];

    public function bisData()
    {
        return $this->hasOne(BisData::class, 'house_number', 'house_number')
        ->where('street_name', $this->street_name);
    }


}
