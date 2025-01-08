<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class envioEmail extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "envio_emails";

    protected $fillable = [
        'id_nit',
        'email',
        'contexto',
    ];
}
