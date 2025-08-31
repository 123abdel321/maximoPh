<?php
namespace App\Helpers;

use DB;
//MODEL
use App\Models\Portafolio\FacDocumentos;
use App\Models\Portafolio\DocumentosGeneral;

class DocumentoGeneralController
{
    public $token = null;
    public function __construct($token_db_portafolio)
    {
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $token_db_portafolio);
    }

    public function bulkDocumentosDelete($token)
	{
        $factura = FacDocumentos::where('token_factura', $this->token)->first();

        if ($factura) {
            $documento = DocumentosGeneral::where('relation_id', $factura->id)
                ->where('relation_type', 2)
                ->delete();
                
            $factura->delete();
        }

        return true;
	}

}