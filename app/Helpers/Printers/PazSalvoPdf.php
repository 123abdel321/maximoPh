<?php

namespace App\Helpers\Printers;

use DB;
use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\ConceptoFacturacion;

class PazSalvoPdf extends AbstractPrinterPdf
{
    public $id_nit;
	public $empresa;

    public function __construct(Empresa $empresa, $id_nit)
	{
		parent::__construct($empresa);

		copyDBConnection('max', 'max');
        setDBInConnection('max', $empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db_portafolio);

		$this->id_nit = $id_nit;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.paz_salvo.paz_salvo';
	}

    public function name()
	{
		return 'paz_salvo_'.uniqid();
	}

    public function paper()
	{
		// if ($this->tipoEmpresion == 1) return 'landscape';
		// if ($this->tipoEmpresion == 2) return 'portrait';

		return 'portrait';
	}

    public function data()
    {
		
		$nit = null;
        $obligaciones = '';
		$getNit = Nits::whereId($this->id_nit)->with('ciudad')->first();
        $inmueblesNit = InmuebleNit::with('inmueble')
            ->where('id_nit', $getNit->id)
            ->get();

        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            $getNit->id,
            [3,7]
        ))->actual()->get();

        $recorrido = 0;
        $totalCount = count($inmueblesNit);
        
        foreach ($inmueblesNit as $inmuebleNit) {
            $recorrido++;

            $separacion = '';
            $tipoPropiedad = 'Propietario';
            $inmuebleNombre = $inmuebleNit->inmueble->nombre;

            if ($inmuebleNit->tipo == 1) $tipoPropiedad = 'Inquilino';
            if ($inmuebleNit->tipo == 2) $tipoPropiedad = 'Inmobiliaria';

            if ($recorrido < $totalCount) $separacion = ', ';

            $obligaciones.="{$tipoPropiedad} del inmueble {$inmuebleNombre}{$separacion}";
        }

        $nombre_administrador = Entorno::where('nombre', 'nombre_administrador')->first();
        $nombre_administrador = $nombre_administrador ? $nombre_administrador->valor : 'SIN NOMBRE DE ADMINISTRADOR';

        $firma_digital = Entorno::where('nombre', 'firma_digital')->first();
        $firma_digital = $firma_digital ? $firma_digital->valor : NULL;
		
		if($getNit){ 
			$nit = (object)[
				'nombre_nit' => $getNit->nombre_completo,
				'telefono' =>  $getNit->telefono_1,
				'email' => $getNit->email,
				'direccion' => $getNit->direccion,
				'tipo_documento' => $getNit->tipo_documento->nombre,
				'numero_documento' => $getNit->numero_documento,
				"ciudad" => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
                'apartamentos' => $getNit->apartamentos
			];
		}

        $baseUrl = config('app.url');
        $idEmpresa = base64_encode($this->empresa->id);
        $idNit = base64_encode($getNit->id);
        $urlValidarArchivo = "{$baseUrl}/paz-y-salvo-publico?code1={$idEmpresa}&code2={$idNit}";
        
        $razonSocial = $this->empresa->razon_social;
        $fechaEmicion = Carbon::now()->format('Y-m-d');
        $fechaVencimiento = Carbon::now()->endOfMonth()->format('Y-m-d');
        $texto = "";

        $texto_marca_agua = false;

        if (!count($extractos)) {//PAZ Y SALVO
            $texto = "$razonSocial hace constar que <b>{$getNit->nombre_completo}</b> con identificación 
                <b>{$nit->tipo_documento} N° {$nit->numero_documento}</b> quien figura en los registros de la copropiedad como 
                <b>$obligaciones</b>, se encuentra a <b>PAZ Y SALVO</b> por <b>todo concepto</b> con la oficina de administración, 
                a la fecha de expedición del presente documento.<br/><br/> 
                Este certificado se expide a solicitud de la interesada el <b>$fechaEmicion</b> y tiene una vigencia hasta el 
                <b>$fechaVencimiento</b>, para los fines que estime convenientes.<br/><br/> 
                Atentamente.";
        } else {//NO ESTA PAZ Y SALVO
            $texto_marca_agua = true;
            $texto = "$razonSocial hace constar que <b>{$getNit->nombre_completo}</b> con identificación 
                <b>{$nit->tipo_documento} N° {$nit->numero_documento}</b>, quien figura en los registros de la copropiedad como 
                <b>$obligaciones</b>, <b>NO se encuentra a PAZ Y SALVO</b> con la oficina de administración, debido a obligaciones pendientes hasta la fecha de expedición del presente documento.<br/><br/>
                Se recomienda al titular regularizar su situación financiera a la mayor brevedad posible para evitar inconvenientes adicionales.<br/><br/>
                Este certificado se expide a solicitud del interesado el <b>$fechaEmicion</b> y refleja el estado de cuenta hasta la fecha mencionada.<br/><br/>
                Atentamente.";
        }

        $svg = QrCode::format('svg')->size(300)->generate($urlValidarArchivo);
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'fecha_pdf' => $fechaEmicion,
            'texto' => $texto,
			'fecha_vencimiento_pdf' => $fechaVencimiento,
            'nombre_administrador' => $nombre_administrador,
            'firma_digital' => $firma_digital,
            'qrCode' => $qrCodeBase64,
            'marca_agua_svg' => $texto_marca_agua,
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
    }

}