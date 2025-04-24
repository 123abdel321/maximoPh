<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminosCondiciones extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "terminos_condiciones";

    protected $fillable = [
        'content',
        'created_by',
        'updated_by'
    ];
}
