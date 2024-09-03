<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'zip_code',
        'street',
        'complement',
        'unit',
        'neighborhood',
        'city',
        'state',
    ];
}
