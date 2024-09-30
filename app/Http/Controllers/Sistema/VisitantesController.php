<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Empresa\Visitantes;

class VisitantesController extends Controller
{
    public function create (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $browser        = "Desconocido";
            $browser_array  = array(
            	'/msie/i'       =>  'Internet Explorer',
            	'/firefox/i'    =>  'Firefox',
            	'/safari/i'     =>  'Safari',
            	'/chrome/i'     =>  'Google Chrome',
            	'/edge/i'       =>  'Edge',
            	'/opera/i'      =>  'Opera',
            	'/netscape/i'   =>  'Netscape',
            	'/maxthon/i'    =>  'Maxthon',
            	'/konqueror/i'  =>  'Konqueror',
            	'/mobile/i'     =>  'Handheld Browser'
            );
            foreach ( $browser_array as $regex => $value ) {
            	if ( preg_match( $regex, $user_agent ) ) {
            		$browser = $value;
            	}
            }

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $os_platform =   "Desconocido";
            $os_array =   array(
            	'/windows nt 10/i'      =>  'Windows 10',
            	'/windows nt 6.3/i'     =>  'Windows 8.1',
            	'/windows nt 6.2/i'     =>  'Windows 8',
            	'/windows nt 6.1/i'     =>  'Windows 7',
            	'/windows nt 6.0/i'     =>  'Windows Vista',
            	'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            	'/windows nt 5.1/i'     =>  'Windows XP',
            	'/windows xp/i'         =>  'Windows XP',
            	'/windows nt 5.0/i'     =>  'Windows 2000',
            	'/windows me/i'         =>  'Windows ME',
            	'/win98/i'              =>  'Windows 98',
            	'/win95/i'              =>  'Windows 95',
            	'/win16/i'              =>  'Windows 3.11',
            	'/macintosh|mac os x/i' =>  'Mac OS X',
            	'/mac_powerpc/i'        =>  'Mac OS 9',
            	'/linux/i'              =>  'Linux',
            	'/ubuntu/i'             =>  'Ubuntu',
            	'/iphone/i'             =>  'iPhone',
            	'/ipod/i'               =>  'iPod',
            	'/ipad/i'               =>  'iPad',
            	'/android/i'            =>  'Android',
            	'/blackberry/i'         =>  'BlackBerry',
            	'/webos/i'              =>  'Mobile'
            );
            foreach ( $os_array as $regex => $value ) {
            	if ( preg_match($regex, $user_agent ) ) {
            		$os_platform = $value;
            	}
            }

            $data = [
                'id_usuario' => $request->user() ? $request->user()->id : null,
                'ip' => $request->get('ip'),
                'device' => $os_platform,
                'browser' => $browser,
            ];

            if ($request->get('ip')) {
            	$geo = Http::withHeaders([
            		'Content-Type' => 'application/json',
            	])->get('ipinfo.io/'.$request->get('ip').'?token=ba8524c502fa55');
            	$responseGeo = (object) $geo->json();

            	if ($responseGeo) {
            		$data = [
						'id_usuario' => $request->user() ? $request->user()->id : null,
						'ip' => $request->get('ip'),
						'device' => $os_platform,
						'browser' => $browser,
            			'loc' => property_exists($responseGeo, 'loc') ? $responseGeo->loc : null,
            			'city' => property_exists($responseGeo, 'city') ? $responseGeo->city : null,
            			'region' => property_exists($responseGeo, 'region') ? $responseGeo->region : null,
            			'country' => property_exists($responseGeo, 'country') ? $responseGeo->country : null,
            			'hostname' => property_exists($responseGeo, 'hostname') ? $responseGeo->hostname : null,
            			'org' => property_exists($responseGeo, 'org') ? $responseGeo->org : null,
            			'timezone' => property_exists($responseGeo, 'timezone') ? $responseGeo->timezone : null,
            		];
            	}
            }

            $visitante = Visitantes::create($data);

            info('Visitante: ', $data);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Visitante creado con exito!'
            ]);
            
        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}