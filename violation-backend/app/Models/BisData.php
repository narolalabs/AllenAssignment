<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BisData extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_number',
        'street_name',
        'legal_adult_use'
    ];
}
