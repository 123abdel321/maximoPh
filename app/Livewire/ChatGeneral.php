<?php

namespace App\Livewire;

use DB;
use Config;
use Carbon\Carbon;
use Livewire\Component;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\Sistema\Chat;
use App\Models\Sistema\Pqrsf;
use App\Models\Sistema\Message;
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
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);
        
        $this->token_db = request()->user()->has_empresa;
        $this->usuario_id = request()->user()->id;
        
        if (auth()->user()->can('mensajes pqrsf')) $this->canalesAdmin[] = 12;
        if (auth()->user()->can('mensajes turnos')) $this->canalesAdmin[] = 14;
        if (auth()->user()->can('mensajes novedades')) $this->canalesAdmin[] = 16;

        $this->cargarChats();
    }

    public function cargarChats()
    {        
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);

        $this->chats = [];
        $this->numeroNotificaciones = 0;

        $chats = DB::connection('max')
            ->table('chats AS CH')
            ->select(
                'CH.id',
                'CH.name',
            )
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
				$query->where('CH.name', 'LIKE', $this->textoBuscarChat.'%');
			})
            ->when($this->relationTypeBuscarChat, function ($query) {
				$query->where('CH.relation_type', $this->relationTypeBuscarChat);
			})
            ->get();

        foreach ($chats as $chat) {
            $ultimo_mensaje = DB::connection('max')
                ->table('messages')
                ->where('chat_id', $chat->id)
                ->orderBy('id', 'DESC')
                ->first();

            $total_mensajes = DB::connection('max')
                ->table('messages')
                ->where('chat_id', $chat->id)
                ->whereNotIn('id', function ($query) {
                    $query->select('message_id')
                        ->from('message_users')
                        ->where('user_id', $this->usuario_id);
                })
                ->count();

            $this->totalMensajes();

            $personas = DB::connection('max')->table('chat_users')->where('user_id', '!=', $this->usuario_id)->get();
            $createdAt =  $ultimo_mensaje ? Carbon::parse($ultimo_mensaje->created_at) : null;
            $now = Carbon::now();
            
            if ($createdAt->isToday()) {
                $ultimo_mensaje->formatted_created_at = $createdAt->format('h:i A');
            } elseif ($createdAt->isYesterday()) {
                $ultimo_mensaje->formatted_created_at = 'Ayer';
            } elseif ($createdAt->isSameYear($now)) {
                $ultimo_mensaje->formatted_created_at = $createdAt->format('d M');
            } else {
                $ultimo_mensaje->formatted_created_at = $createdAt->format('d M Y');
            }

            //SIRVE PARA NOTIFICARLE AL OBSERVER QUE ACTUALICE LOS MENSAJES
            $mensajeDisparador = Message::where('chat_id', $chat->id)
                ->where('user_id', '!=', $this->usuario_id)
                ->where('status', 1)
                ->first();

            if ($mensajeDisparador) {
                $mensajeDisparador->status = 2;
                $mensajeDisparador->save();

                Message::where('chat_id', $chat->id)
                    ->where('user_id', '!=', $this->usuario_id)
                    ->where('status', 1)
                    ->update([
                        'status' => 2
                    ]);
            }

            $this->chats[] = (object)[
                'id' => $chat->id,
                'nombre' => $chat->name,
                'ultimo_mensaje' => $ultimo_mensaje,
                'total_mensajes' => $total_mensajes,
                'personas' => $personas
            ];
        }
    }

    public function cargarMensajes($chatId = null, $observador = true)
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);

        $chat = DB::connection('max')->table('chats')->where('id', $chatId)->first();
        $this->mensajeActivoId = $chatId;

        if ($chat) {
            
            $this->mensajes = (object)[
                'nombre' => $chat->name,
                'avatar' => '',
                'relation_type' => $chat->relation_type,
                'relation_module' => $this->getRelationModule($chat),
                'mensajes' => []
            ];
    
            $this->mensajes->mensajes = DB::connection('max')
                ->table('messages AS ME')
                ->select(
                    'ME.id',
                    'ME.content',
                    'ME.user_id',
                    'ME.status',
                    'ME.created_at'
                )
                ->where('chat_id', $chatId)
                ->get();
                    
            foreach ($this->mensajes->mensajes as $key => $mensaje) {
                $archivos = DB::connection('max')
                    ->table('archivos_generales AS AG')
                    ->where('relation_type', '17')
                    ->where('relation_id', $mensaje->id)
                    ->get();

                $usuario = DB::connection('clientes')
                    ->table('users')
                    ->select('firstname', 'lastname')
                    ->where('id', $mensaje->user_id)
                    ->first();
                    
                $this->mensajes->mensajes[$key]->archivos = $archivos;
                $this->mensajes->mensajes[$key]->usuario = $usuario;

                MessageUser::firstOrCreate([
                    'message_id' => $mensaje->id,
                    'user_id' => $this->usuario_id,
                ]);
            }
            //SIRVE PARA NOTIFICARLE AL OBSERVER QUE ACTUALICE LOS MENSAJES
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
                    ->update([
                        'status' => 3
                    ]);
            }
        } else {
            $this->mensajes = false;
        }
    }

    public function totalMensajes()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);

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
                    ->where('user_id', $this->usuario_id); // Excluye mensajes ya leídos
            })
            ->count();
            
        $this->numeroNotificaciones+= $totalMensajes;
    }

    public function volverChat()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);

        $this->mensajeActivoId = null;
        $this->cargarChats();
    }

    public function enviarMensaje()
    {
        if (!$this->textoEscrito) return;

        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);
        
        DB::connection('max')->beginTransaction();

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

        event(new PrivateMessageEvent('mensajeria-'.$this->token_db, [
            'chat_id' => $this->mensajeActivoId,
            'action' => 'creacion_mensaje',
            'user_id' => $this->usuario_id,
        ]));

        DB::connection('max')->commit();

        $this->cargarMensajes($this->mensajeActivoId, false);

        $this->textoEscrito = '';
    }

    public function actualizarEstado($chatId, $estado)
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->token_db);

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
                ->update([
                    'estado' => $estado
                ]);

            $mensajeText = 'Se ha cambiado el estado del pqrsf a '.$nombreEstado;
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

    public function render()
    {
        return view('livewire.chat-general');
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

                if ($relationModule->estado == 0) {
                    Pqrsf::where('id', $chat->relation_id)
                        ->update([
                            'estado' => 3
                        ]);
                }
                break;
            
            default:
                # code...
                break;
        }

        return $relationModule;
    }
}
