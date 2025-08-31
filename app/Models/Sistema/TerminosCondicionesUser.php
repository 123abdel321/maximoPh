<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminosCondicionesUser extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "terminos_condiciones_users";

    protected $fillable = [
        'user_id',
        'terminos_condiciones_id',
    ];
}
