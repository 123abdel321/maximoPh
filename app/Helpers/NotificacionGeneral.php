<?php
namespace App\Helpers;

use DB;
use Carbon\Carbon;
use App\Events\PrivateMessageEvent;
//MODEL
use App\Models\Sistema\Notificaciones;

class NotificacionGeneral
{
    public $modelo_padre;
    public $id_remitente;
    public $id_destinatario;
    public $id_notificacion;

    public function __construct($id_remitente = null, $id_destinatario = null, $modelo_padre = null)
    {
        $this->modelo_padre = $modelo_padre;
        $this->id_remitente = $id_remitente;
        $this->id_destinatario = $id_destinatario;
    }

    public function crear($data, $recurrentes = null)
    {
        $notificacion = new Notificaciones([
            'id_usuario' => $data->id_usuario,
            'mensaje' => $data->mensaje,
            'function' => $data->function,
            'data' => $data->data,
            'estado' => $data->estado,
            'created_by' => $data->created_by,
            'updated_by' => $data->updated_by,
        ]);
        
        $notificacion->notificacion()->associate($this->modelo_padre);
        $this->modelo_padre->notificacion()->save($notificacion);
        $notificacion->save();

        if ($recurrentes) $this->hideRecurrentes($data);

        return $notificacion->id;
    }

    public function notificar($chanel, $data)
    {
        event(new PrivateMessageEvent($chanel, $data));
    }

    private function hideRecurrentes($data)
    {
        return 0;
    }

}