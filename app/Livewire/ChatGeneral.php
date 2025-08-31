<?php

namespace App\Livewire;

use DB;
use Carbon\Carbon;
use Livewire\Component;
use App\Events\PrivateMessageEvent;
// MODELS
use App\Models\Sistema\Chat;
use App\Models\Sistema\Turno;
use App\Models\Sistema\Pqrsf;
use App\Models\Sistema\Message;
use App\Models\Sistema\Porteria;
use App\Models\Sistema\Novedades;
use App\Models\Sistema\MessageUser;
use App\Models\Sistema\ArchivosGenerales;

class ChatGeneral extends Component
{
    public $count = 1;
    public $chats = [];
    public $mensajes = false;
    public $token_db = null;
    public $usuario_id = null;
    public $canalesAdmin = [];
    public $textoEscrito = null;
    public $pantallaSize = false;
    public $mensajeActivoId = null;
    public $textoBuscarChat = '';
    public $numeroNotificaciones = 0;
    public $relationTypeBuscarChat = 0;

    protected $listeners = [
        'agregarChats' => 'agregarChats',
        'cargarChats' => 'cargarChats',
        'cargarMensajes' => 'cargarMensajes',
        'enviarMensaje' => 'enviarMensaje',
        'actualizarEstado' => 'actualizarEstado'
    ];

    public function mount()
    {
        $this->initializeDatabaseConnection();
        $this->setUserPermissions();
        $this->cargarChats();
    }

    private function initializeDatabaseConnection()
    {
        copyDBConnection('max', 'max');
        $this->token_db = request()->user()->has_empresa;
        $this->usuario_id = request()->user()->id;
        setDBInConnection('max', $this->token_db);
    }

    private function setUserPermissions()
    {
        if (auth()->user()->can('mensajes pqrsf')) $this->canalesAdmin[] = 12;
        if (auth()->user()->can('mensajes turnos')) $this->canalesAdmin[] = 14;
        if (auth()->user()->can('mensajes porteria')) $this->canalesAdmin[] = 10;
        if (auth()->user()->can('mensajes novedades')) $this->canalesAdmin[] = 16;
    }

    public function cargarChats()
    {
        $this->chats = [];
        $this->numeroNotificaciones = 0;
        $this->initializeDatabaseConnection();

        $chats = $this->getChatsQuery()->get();

        foreach ($chats as $chat) {
            $ultimo_mensaje = $this->getUltimoMensaje($chat->id);
            $total_mensajes = $this->getTotalMensajes($chat->id);
            $personas = $this->getPersonas($chat->id);
            $responsable = $this->getResponsable($chat);

            $this->totalMensajes();
            $this->updateMensajeStatus($chat->id);

            $this->chats[] = (object)[
                'id' => $chat->id,
                'nombre' => $chat->name,
                'ultimo_mensaje' => $ultimo_mensaje,
                'total_mensajes' => $total_mensajes,
                'personas' => $personas,
                'responsable' => $responsable
            ];
        }
    }

    private function getChatsQuery()
    {
        return DB::connection('max')
            ->table('chats AS CH')
            ->select('CH.id', 'CH.name', 'CH.relation_id', 'CH.relation_type')
            ->where(function ($query) {
                $query->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('chat_users AS CU')
                        ->whereColumn('CU.chat_id', 'CH.id')
                        ->where('CU.user_id', $this->usuario_id);
                })
                ->orWhereIn('CH.relation_type', $this->canalesAdmin);
            })
            ->when($this->textoBuscarChat, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('CH.name', 'LIKE', $this->textoBuscarChat . '%')
                        ->orWhereExists(function ($subSubQuery) {
                            $subSubQuery->select(DB::raw(1))
                                ->from('chat_users AS CU')
                                ->join(
                                    DB::connection('clientes')->getDatabaseName() . '.users AS U',
                                    'CU.user_id',
                                    '=',
                                    'U.id'
                                )
                                ->whereColumn('CU.chat_id', 'CH.id')
                                ->where(function ($nameQuery) {
                                    $nameQuery->where('U.firstname', 'LIKE', $this->textoBuscarChat . '%')
                                        ->orWhere('U.lastname', 'LIKE', $this->textoBuscarChat . '%');
                                });
                        });
                });
            })
            ->when($this->relationTypeBuscarChat, function ($query) {
                $query->where('CH.relation_type', $this->relationTypeBuscarChat);
            })
            ->orderBy(DB::raw('(SELECT MES.created_at FROM messages AS MES WHERE MES.chat_id = CH.id ORDER BY MES.created_at DESC LIMIT 1)'), 'DESC');
    }

    private function getUltimoMensaje($chatId)
    {
        $ultimo_mensaje = DB::connection('max')
            ->table('messages')
            ->where('chat_id', $chatId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($ultimo_mensaje) {
            $ultimo_mensaje->formatted_created_at = $this->formatCreatedAt($ultimo_mensaje->created_at);
        }

        return $ultimo_mensaje;
    }

    private function getTotalMensajes($chatId)
    {
        return DB::connection('max')
            ->table('messages')
            ->where('chat_id', $chatId)
            ->whereNotIn('id', function ($query) {
                $query->select('message_id')
                    ->from('message_users')
                    ->where('user_id', $this->usuario_id);
            })
            ->count();
    }

    private function getPersonas($chatId)
    {
        return DB::connection('max')->table('chat_users')->where('chat_id', $chatId)->where('user_id', '!=', $this->usuario_id)->get();
    }

    private function getResponsable($chat)
    {
        $idUsuario = null;

        switch ($chat->relation_type) {
            case 10:
                $porteria = Porteria::where('id', $chat->relation_id)->first();
                $idUsuario = $porteria ? $porteria->id_usuario : null;
                break;

            case 12:
                $pqrsf = Pqrsf::where('id', $chat->relation_id)->first();
                $idUsuario = $pqrsf ? $pqrsf->created_by : null;
                break;

            case 14:
                $turno = Turno::where('id', $chat->relation_id)->first();
                $idUsuario = $turno ? $turno->id_usuario : null;
                break;

            case 16:
                $novedad = Novedades::where('id', $chat->relation_id)->first();
                if ($novedad && $novedad->id_porteria) {
                    $porteria = Porteria::where('id', $novedad->id_porteria)->first();
                    $idUsuario = $porteria ? $porteria->id_usuario : null;
                }
                break;
        }

        return $idUsuario ? DB::connection('clientes')->table('users')->where('id', $idUsuario)->first() : null;
    }

    private function formatCreatedAt($createdAt)
    {
        $createdAt = Carbon::parse($createdAt);
        $now = Carbon::now();

        if ($createdAt->isToday()) {
            return $createdAt->format('h:i A');
        } elseif ($createdAt->isYesterday()) {
            return 'Ayer';
        } elseif ($createdAt->isSameYear($now)) {
            return $createdAt->format('d M');
        } else {
            return $createdAt->format('d M Y');
        }
    }

    private function updateMensajeStatus($chatId)
    {
        $mensajeDisparador = Message::where('chat_id', $chatId)
            ->where('user_id', '!=', $this->usuario_id)
            ->where('status', 1)
            ->first();

        if ($mensajeDisparador) {
            $mensajeDisparador->status = 2;
            $mensajeDisparador->save();

            Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $this->usuario_id)
                ->where('status', 1)
                ->update(['status' => 2]);
        }
    }

    public function cargarMensajes($chatId = null, $observador = true)
    {
        $this->initializeDatabaseConnection();

        $chat = DB::connection('max')->table('chats')->where('id', $chatId)->first();
        $this->mensajeActivoId = $chatId;

        if ($chat) {
            $this->mensajes = (object)[
                'nombre' => $chat->name,
                'avatar' => '',
                'relation_type' => $chat->relation_type,
                'relation_module' => $this->getRelationModule($chat),
                'mensajes' => $this->getMensajes($chatId)
            ];

            $this->updateMensajeStatusForObserver($chatId, $observador);
        } else {
            $this->mensajes = false;
        }
    }

    private function getMensajes($chatId)
    {
        $mensajes = DB::connection('max')
            ->table('messages AS ME')
            ->select('ME.id', 'ME.content', 'ME.user_id', 'ME.status', 'ME.created_at')
            ->where('chat_id', $chatId)
            ->get();

        foreach ($mensajes as $key => $mensaje) {
            $mensaje->archivos = $this->getArchivos($mensaje->id);
            $mensaje->usuario = $this->getUsuario($mensaje->user_id);
            $mensaje->created_at = $this->formatMensajeCreatedAt($mensaje->created_at);

            MessageUser::firstOrCreate([
                'message_id' => $mensaje->id,
                'user_id' => $this->usuario_id,
            ]);
        }

        return $mensajes;
    }

    private function getArchivos($mensajeId)
    {
        return DB::connection('max')
            ->table('archivos_generales AS AG')
            ->where('relation_type', '17')
            ->where('relation_id', $mensajeId)
            ->get();
    }

    private function getUsuario($userId)
    {
        return DB::connection('clientes')
            ->table('users')
            ->select('firstname', 'lastname')
            ->where('id', $userId)
            ->first();
    }

    private function formatMensajeCreatedAt($createdAt)
    {
        $createdAt = Carbon::parse($createdAt);
        return $createdAt->isToday()
            ? $createdAt->format('H:i')
            : ($createdAt->isYesterday()
                ? 'Ayer ' . $createdAt->format('H:i')
                : $createdAt->format('d/m/Y H:i'));
    }

    private function updateMensajeStatusForObserver($chatId, $observador)
    {
        $mensajeDisparador = Message::where('chat_id', $chatId)
            ->where('user_id', '!=', $this->usuario_id)
            ->whereIn('status', [1, 2])
            ->first();

        if ($mensajeDisparador && $observador) {
            $mensajeDisparador->status = 3;
            $mensajeDisparador->save();
            Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $this->usuario_id)
                ->whereIn('status', [1, 2])
                ->update(['status' => 3]);
        }
    }

    public function totalMensajes()
    {
        $this->initializeDatabaseConnection();

        $totalMensajes = DB::connection('max')
            ->table('chats AS CH')
            ->where(function ($query) {
                $query->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('chat_users AS CU')
                        ->whereColumn('CU.chat_id', 'CH.id')
                        ->where('CU.user_id', $this->usuario_id);
                })
                ->orWhereIn('CH.relation_type', $this->canalesAdmin);
            })
            ->join('messages AS ME', 'CH.id', '=', 'ME.chat_id')
            ->whereNotIn('ME.id', function ($query) {
                $query->select('message_id')
                    ->from('message_users')
                    ->where('user_id', $this->usuario_id);
            })
            ->count();

        $this->numeroNotificaciones += $totalMensajes;
    }

    public function volverChat()
    {
        $this->initializeDatabaseConnection();
        $this->mensajeActivoId = null;
        $this->cargarChats();
    }

    public function enviarMensaje()
    {
        if (!$this->textoEscrito) return;

        $this->initializeDatabaseConnection();

        DB::connection('max')->beginTransaction();

        try {
            $mensaje = Message::create([
                'chat_id' => $this->mensajeActivoId,
                'user_id' => $this->usuario_id,
                'content' => $this->textoEscrito,
                'status' => 1
            ]);

            MessageUser::firstOrCreate([
                'message_id' => $mensaje->id,
                'user_id' => $this->usuario_id,
            ]);

            event(new PrivateMessageEvent('mensajeria-' . $this->token_db, [
                'chat_id' => $this->mensajeActivoId,
                'action' => 'creacion_mensaje',
                'user_id' => $this->usuario_id,
            ]));

            DB::connection('max')->commit();

            $this->cargarMensajes($this->mensajeActivoId, false);
            $this->textoEscrito = '';
        } catch (\Exception $e) {
            DB::connection('max')->rollBack();
            // Manejar el error según sea necesario
        }
    }

    public function actualizarEstado($chatId, $estado)
    {
        $this->initializeDatabaseConnection();

        $chat = DB::connection('max')
            ->table('chats AS CH')
            ->where('id', $chatId)
            ->first();

        $mensajeText = '';

        $nombreEstado = '<b class="pqrsf-chat-mensaje-activo">Activo</b>';
        if ($estado == 1) $nombreEstado = '<b class="pqrsf-chat-mensaje-proceso">En proceso</b>';
        if ($estado == 2) $nombreEstado = '<b class="pqrsf-chat-mensaje-cerrado">Cerrado</b>';

        if ($chat->relation_type == 12) {
            Pqrsf::where('id', $chat->relation_id)
                ->update(['estado' => $estado]);

            $mensajeText = 'Se ha cambiado el estado del pqrsf a ' . $nombreEstado;
        }

        if ($chat->relation_type == 14) {
            Turno::where('id', $chat->relation_id)
                ->update(['estado' => $estado]);

            $mensajeText = 'Se ha cambiado el estado del turno a ' . $nombreEstado;
        }

        $this->textoEscrito = $mensajeText;
        $this->enviarMensaje();
    }

    public function filtroChats()
    {
        $this->cargarChats();
    }

    public function agregarFiltroTypo($relation_type)
    {
        $this->relationTypeBuscarChat = (int)$relation_type;
        $this->cargarChats();
    }

    private function getRelationModule($chat)
    {
        if (!$chat->relation_type) return [];

        $relationModule = [];

        switch ($chat->relation_type) {
            case 12://PQRSF
                $relationModule = DB::connection('max')
                    ->table('pqrsf')
                    ->where('id', $chat->relation_id)
                    ->first();
                if ($relationModule->estado == 0 && $chat->created_by != $this->usuario_id) {
                    Pqrsf::where('id', $chat->relation_id)
                        ->update(['estado' => 3]);
                }
                break;

            case 14://TURNOS
                $relationModule = DB::connection('max')
                    ->table('turnos')
                    ->where('id', $chat->relation_id)
                    ->first();
                if ($relationModule->estado == 0 && $chat->created_by == $this->usuario_id) {
                    Turno::where('id', $chat->relation_id)
                        ->update(['estado' => 3]);
                }
                break;

            case 16://NOVEDADES
                $relationModule = DB::connection('max')
                    ->table('novedades')
                    ->where('id', $chat->relation_id)
                    ->first();
                break;

            default:
                break;
        }

        return $relationModule;
    }

    public function render()
    {
        return view('livewire.chat-general.chat-general');
    }
}