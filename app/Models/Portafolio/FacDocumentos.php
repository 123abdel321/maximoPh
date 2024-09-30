<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacDocumentos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_documentos";

    protected $fillable = [
        'id_comprobante',
        'id_nit',
        'fecha_manual',
        'consecutivo',
        'token_factura',
        'debito',
        'credito',
        'saldo_final',
        'anulado',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function documentos()
    {
        return $this->morphMany('App\Models\Portafolio\DocumentosGeneral', 'relation');
	}
}
