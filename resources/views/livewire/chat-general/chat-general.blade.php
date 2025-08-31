<div class="offcanvas offcanvas-end" tabindex="-1" id="chatMaximo" aria-labelledby="chatMaximoLabel" style="background-color: #111b21;" wire:ignore.self>
    <style>
        .item-grupos-chat {
            background-color: #111b21;
            padding-right: 0px;
        }
        .list-group-item {
            background: transparent;
            color: white;
            cursor: pointer;
        }
        .list-group-item:hover {
            background-color: #243946;
            color: white;
        }
        .chat-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        .component-chat:hover {
            background-color: #28353c;
        }
        .componente-chat-mensajes {
            background: linear-gradient(rgba(17, 27, 33, 0.9), rgba(17, 27, 33, 0.9)), url('https://static.whatsapp.net/rsrc.php/v4/yl/r/gi_DckOUM5a.png');
            z-index: 1;
            padding-left: 0px;
            padding-right: 0px;
        }
        .icono-status-no_entregado {
            margin-top: -6px;
            margin-left: 5px;
            font-size: 12px;
            color: #cecece;
        }
        .icono-status-entregado {
            margin-top: -6px;
            margin-left: 5px;
            font-size: 12px;
            color: #cecece;
        }
        .icono-status-leido {
            margin-top: -6px;
            margin-left: 5px;
            font-size: 12px;
            color: #53bdeb;
        }
        .container-chat {
            width: 67%;
            position: absolute;
            bottom: 0;
            background-color: #202c33;
            padding: 10px;
            display: flex;
        }
        .icon-send {
            font-size: 17px;
            align-self: center;
            margin-left: 5px;
            margin-right: 0px;
            cursor: pointer;
        }
        .icon-action {
            font-size: 20px;
            margin-left: -2px;
            margin-right: 7px;
            cursor: pointer;
        }
        #mensaje-body {
            contain: content;
            padding: 5px 10px 10px 10px;
            overflow: hidden;
            background: linear-gradient(rgba(17, 27, 33, 0.9), rgba(17, 27, 33, 0.9)), url(https://static.whatsapp.net/rsrc.php/v4/yl/r/gi_DckOUM5a.png);
        }
        #input-search {
            background-color: #2c3c45;
            color: #cacaca;
            border: solid 1px #2c3c45;
        }
        .button-search {
            cursor: pointer;
            background-color: #2c3c45;
            color: #cacaca;
            border: solid 1px #2c3c45;
        }
        .button-clean-search {
            cursor: pointer;
            background-color: #2c3c45;
            color: #00a884;
            border: solid 1px #2c3c45;
        }
        .input-group.focused .input-group-text {
            border-color: transparent !important;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1) !important;
        }
        .btn-filter-chat-types {
            margin-left: 5px !important;
            border-radius: 10px !important;
            margin-bottom: 0px !important;
            padding: 5px 10px 5px 10px !important;
            box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 0px 0px 0px 0px rgb(0 0 0 / 57%) !important;
        }
        .btn-check:checked + .btn-filter-chat-types {
            background-color: #0a332c !important;
            border-color: #0a332c !important;
            color: #00a884 !important;
        }
        .btn-check + .btn-filter-chat-types {
            background-color: #2c3c45  !important;
            border-color: #2c3c45  !important;
            color: #8696a0 !important;
        }
        .ultimo-mensaje {
            font-size: 10px;
            color: #fff;
            font-weight: 500;
            padding-right: 0px;
            padding-left: 0px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .nombre-usuario {
            font-size: 10px;
            color: springgreen;
        }

        .formato-fecha {
            font-size: 10px;
            margin-bottom: 0;
            font-weight: 500;
            float: inline-end;
            margin-top: -6px;
        }

        .formato-fecha-propio {
            font-size: 10px;
            margin-bottom: 0;
            font-weight: 500;
            text-align: end;
            margin-top: -10px;
            color: #cecece;
        }
        
        .texto-mensaje {
            font-size: 13px;
            margin-bottom: 5px;
            text-align-last: auto;
            font-weight: 600;
            padding-right: 40px;
        }

        .button-action-chat {
            width: 50px;
            height: 50px;
            text-align: -webkit-center;
        }
        
        .icon-action-chat {
            padding: 10px;
            background-color: #2a3942;
            font-size: 15px;
            border-radius: 50%;
            cursor: pointer;
        }

        .icon-action-chat:hover {
            background-color: #34637f;
        }

        .turquoise {
            color: turquoise;
        }

        .deepskyblue {
            color: deepskyblue;
        }

        .springgreen {
            color: springgreen;
        }

        .nombre-container-estados {
            color: #d5d5d5;
            font-weight: 400;
            font-size: 15px;
            position: absolute;
            margin-top: -12px;
            margin-left: -40px;
        }
    </style>
    <!-- CHATS -->
    <div style="{{ $mensajeActivoId ? 'display: none !important;' : '' }} display: flex; padding: 15px;">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" style="margin-top: 6px; margin-left: 5px;"></button>
        <h5 class="modal-title" style="margin-left: 10px;">Chats</h5>
    </div>
    <div style="{{ $mensajeActivoId ? 'display: none !important;' : '' }} padding: 15px;">
        <div class="form-group">
            <div class="input-group mb-4">
                <span class="button-clean-search input-group-text" style="cursor: pointer; display: none;">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span class="button-search input-group-text" style="cursor: pointer; border-radius: 10px 0px 0px 10px;">
                    <i class="fas fa-search"></i>
                </span>
                <input
                    wire:model.debounce.500ms="textoBuscarChat" 
                    id="input-search"
                    wire:model="textoBuscarChat" 
                    wire:keyup="filtroChats"
                    class="form-control"
                    placeholder="Buscar"
                    type="text"
                >
            </div>
        </div>
        <div>
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
                <label
                    wire:click="agregarFiltroTypo(0)"
                    class="btn-filter-chat-types btn btn-sm btn-outline-primary"
                    for="btnradio1">Todos
                </label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
                <label
                    wire:click="agregarFiltroTypo(12)"
                    class="btn-filter-chat-types btn btn-sm btn-outline-primary"
                    for="btnradio2">Pqrsf
                </label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
                <label
                    wire:click="agregarFiltroTypo(14)"
                    class="btn-filter-chat-types btn btn-sm btn-outline-primary"
                    for="btnradio3">Turnos
                </label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio4" autocomplete="off">
                <label
                    wire:click="agregarFiltroTypo(16)"
                    class="btn-filter-chat-types btn btn-sm btn-outline-primary"
                    for="btnradio4">Novedades
                </label>
            </div>
        </div>
    </div>
    <div id="chat-body" class="offcanvas-body wrapper" style="{{ $mensajeActivoId ? 'display: none !important;' : '' }} contain: content; padding: 0px;">


        <div wire:loading.class.remove="d-none" class="d-none" style="
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 1000;
            ">
            <div class="spinner-border"></div>
        </div>

        @foreach($chats as $chat)
            <div
                wire:click="cargarMensajes({{ $chat->id }})"
                wire:key="chat-{{ $chat->id }}"
                id="component-chat"
                class="component-chat"
                style="padding: 10px; cursor: pointer; display: flex; justify-content: space-between; border-bottom: solid 1px #14222a;"
            >
                <div class="" style="padding-left: 0px;">
                    <svg viewBox="0 0 212 212" height="45" width="45" preserveAspectRatio="xMidYMid meet" class="xh8yej3 x5yr21d" version="1.1" x="0px" y="0px" enable-background="new 0 0 212 212">
                        <title>default-user</title>
                        <path style="opacity: 0.6;" fill="#DFE5E7" d="M106.251,0.5C164.653,0.5,212,47.846,212,106.25S164.653,212,106.25,212C47.846,212,0.5,164.654,0.5,106.25 S47.846,0.5,106.251,0.5z" class="xl21vc0">
                        </path>
                        <g>
                            <path fill="#FFFFFF" d="M173.561,171.615c-0.601-0.915-1.287-1.907-2.065-2.955c-0.777-1.049-1.645-2.155-2.608-3.299 c-0.964-1.144-2.024-2.326-3.184-3.527c-1.741-1.802-3.71-3.646-5.924-5.47c-2.952-2.431-6.339-4.824-10.204-7.026 c-1.877-1.07-3.873-2.092-5.98-3.055c-0.062-0.028-0.118-0.059-0.18-0.087c-9.792-4.44-22.106-7.529-37.416-7.529 s-27.624,3.089-37.416,7.529c-0.338,0.153-0.653,0.318-0.985,0.474c-1.431,0.674-2.806,1.376-4.128,2.101 c-0.716,0.393-1.417,0.792-2.101,1.197c-3.421,2.027-6.475,4.191-9.15,6.395c-2.213,1.823-4.182,3.668-5.924,5.47 c-1.161,1.201-2.22,2.384-3.184,3.527c-0.964,1.144-1.832,2.25-2.609,3.299c-0.778,1.049-1.464,2.04-2.065,2.955 c-0.557,0.848-1.033,1.622-1.447,2.324c-0.033,0.056-0.073,0.119-0.104,0.174c-0.435,0.744-0.79,1.392-1.07,1.926 c-0.559,1.068-0.818,1.678-0.818,1.678v0.398c18.285,17.927,43.322,28.985,70.945,28.985c27.678,0,52.761-11.103,71.055-29.095 v-0.289c0,0-0.619-1.45-1.992-3.778C174.594,173.238,174.117,172.463,173.561,171.615z" class="x1d6ck0k"></path>
                            <path fill="#FFFFFF" d="M106.002,125.5c2.645,0,5.212-0.253,7.68-0.737c1.234-0.242,2.443-0.542,3.624-0.896 c1.772-0.532,3.482-1.188,5.12-1.958c2.184-1.027,4.242-2.258,6.15-3.67c2.863-2.119,5.39-4.646,7.509-7.509 c0.706-0.954,1.367-1.945,1.98-2.971c0.919-1.539,1.729-3.155,2.422-4.84c0.462-1.123,0.872-2.277,1.226-3.458 c0.177-0.591,0.341-1.188,0.49-1.792c0.299-1.208,0.542-2.443,0.725-3.701c0.275-1.887,0.417-3.827,0.417-5.811 c0-1.984-0.142-3.925-0.417-5.811c-0.184-1.258-0.426-2.493-0.725-3.701c-0.15-0.604-0.313-1.202-0.49-1.793 c-0.354-1.181-0.764-2.335-1.226-3.458c-0.693-1.685-1.504-3.301-2.422-4.84c-0.613-1.026-1.274-2.017-1.98-2.971 c-2.119-2.863-4.646-5.39-7.509-7.509c-1.909-1.412-3.966-2.643-6.15-3.67c-1.638-0.77-3.348-1.426-5.12-1.958 c-1.181-0.355-2.39-0.655-3.624-0.896c-2.468-0.484-5.035-0.737-7.68-0.737c-21.162,0-37.345,16.183-37.345,37.345 C68.657,109.317,84.84,125.5,106.002,125.5z" class="x1d6ck0k"></path>
                        </g>
                    </svg>
                </div>
                <div class="" style="align-self: center; margin-right: auto; padding-left: 20px;">
                    @if ($chat->total_mensajes)
                        <b style="font-size: 14px; color: white; font-weight: 600; padding-right: 0px; padding-left: 0px;">
                            {{ $chat->nombre }}
                            @if ($chat->responsable)
                                @if ($chat->responsable->id != $usuario_id)
                                    - {{ $chat->responsable->firstname }} {{ $chat->responsable->lastname }}
                                @endif
                            @endif
                        </b><br/>
                        <b class="ultimo-mensaje">
                            {!! $chat->ultimo_mensaje->content !!}
                        </b>
                    @else
                        <b style="font-size: 14px; color: white; font-weight: 400; padding-right: 0px; padding-left: 0px;">
                            {{ $chat->nombre }}
                            @if ($chat->responsable)
                                @if ($chat && $chat->responsable->id != $usuario_id)
                                    - {{ $chat->responsable->firstname }} {{ $chat->responsable->lastname }}
                                @endif
                            @endif
                        </b><br/>
                        <b class="ultimo-mensaje">{!! $chat->ultimo_mensaje->content !!}</b>
                    @endif
                </div>
                <div class="" style="align-self: center; padding-right: 0px;">
                    @if ($chat->total_mensajes)
                        <b style="font-size: 13px; color: #00a884;; font-weight: 700; text-wrap-mode: nowrap; float: inline-end;">{{ $chat->ultimo_mensaje->formatted_created_at }}</b><br/>
                        <b style="font-size: 13px; color: #ffffff; font-weight: bold; background-color: #00a884; padding: 0px 6px 0px 6px; border-radius: 50px; float: right;">{{ $chat->total_mensajes }}</b>
                    @else
                        <b class="ultimo-mensaje" style="font-size: 13px; color: #a2b0ce; font-weight: 500; align-self: start; margin-top: 5px; text-wrap-mode: nowrap; float: inline-end;">{{ $chat->ultimo_mensaje->formatted_created_at }}</b>
                    @endif
                </div>
            </div>
        @endforeach
        @if (!count($chats))
            <h3 style="height: 90%; place-content: center; place-self: center; color: #8b9c9c;">SIN RESULTADOS</h3>  
        @endif
    </div>
    <!-- END CHATS -->
    <!-- MENSAJES -->
    <input
        id="input-numero-notificaciones-chat"
        class="form-control"
        value="{{ $numeroNotificaciones }}"
        type="text"
        style="display: none;"
    >
    <input
        id="id-mensaje-abierto"
        value="{{ $mensajeActivoId }}"
        style="display: none;"
    >
    <div class="" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }} padding: 10px; background-color: #202c33; box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 3px 3px 2px rgb(0 0 0 / 21%); display: flex;">
        <i
            wire:click="volverChat()"
            style="font-size: 17px; color: white; align-content: center; padding: 10px; cursor: pointer;"
            class="fas fa-chevron-left"
        ></i>
        <svg viewBox="0 0 212 212" height="45" width="45" preserveAspectRatio="xMidYMid meet" class="xh8yej3 x5yr21d" version="1.1" x="0px" y="0px" enable-background="new 0 0 212 212">
            <title>default-user</title>
            <path style="opacity: 0.6;" fill="#DFE5E7" d="M106.251,0.5C164.653,0.5,212,47.846,212,106.25S164.653,212,106.25,212C47.846,212,0.5,164.654,0.5,106.25 S47.846,0.5,106.251,0.5z" class="xl21vc0">
            </path>
            <g>
                <path fill="#FFFFFF" d="M173.561,171.615c-0.601-0.915-1.287-1.907-2.065-2.955c-0.777-1.049-1.645-2.155-2.608-3.299 c-0.964-1.144-2.024-2.326-3.184-3.527c-1.741-1.802-3.71-3.646-5.924-5.47c-2.952-2.431-6.339-4.824-10.204-7.026 c-1.877-1.07-3.873-2.092-5.98-3.055c-0.062-0.028-0.118-0.059-0.18-0.087c-9.792-4.44-22.106-7.529-37.416-7.529 s-27.624,3.089-37.416,7.529c-0.338,0.153-0.653,0.318-0.985,0.474c-1.431,0.674-2.806,1.376-4.128,2.101 c-0.716,0.393-1.417,0.792-2.101,1.197c-3.421,2.027-6.475,4.191-9.15,6.395c-2.213,1.823-4.182,3.668-5.924,5.47 c-1.161,1.201-2.22,2.384-3.184,3.527c-0.964,1.144-1.832,2.25-2.609,3.299c-0.778,1.049-1.464,2.04-2.065,2.955 c-0.557,0.848-1.033,1.622-1.447,2.324c-0.033,0.056-0.073,0.119-0.104,0.174c-0.435,0.744-0.79,1.392-1.07,1.926 c-0.559,1.068-0.818,1.678-0.818,1.678v0.398c18.285,17.927,43.322,28.985,70.945,28.985c27.678,0,52.761-11.103,71.055-29.095 v-0.289c0,0-0.619-1.45-1.992-3.778C174.594,173.238,174.117,172.463,173.561,171.615z" class="x1d6ck0k"></path>
                <path fill="#FFFFFF" d="M106.002,125.5c2.645,0,5.212-0.253,7.68-0.737c1.234-0.242,2.443-0.542,3.624-0.896 c1.772-0.532,3.482-1.188,5.12-1.958c2.184-1.027,4.242-2.258,6.15-3.67c2.863-2.119,5.39-4.646,7.509-7.509 c0.706-0.954,1.367-1.945,1.98-2.971c0.919-1.539,1.729-3.155,2.422-4.84c0.462-1.123,0.872-2.277,1.226-3.458 c0.177-0.591,0.341-1.188,0.49-1.792c0.299-1.208,0.542-2.443,0.725-3.701c0.275-1.887,0.417-3.827,0.417-5.811 c0-1.984-0.142-3.925-0.417-5.811c-0.184-1.258-0.426-2.493-0.725-3.701c-0.15-0.604-0.313-1.202-0.49-1.793 c-0.354-1.181-0.764-2.335-1.226-3.458c-0.693-1.685-1.504-3.301-2.422-4.84c-0.613-1.026-1.274-2.017-1.98-2.971 c-2.119-2.863-4.646-5.39-7.509-7.509c-1.909-1.412-3.966-2.643-6.15-3.67c-1.638-0.77-3.348-1.426-5.12-1.958 c-1.181-0.355-2.39-0.655-3.624-0.896c-2.468-0.484-5.035-0.737-7.68-0.737c-21.162,0-37.345,16.183-37.345,37.345 C68.657,109.317,84.84,125.5,106.002,125.5z" class="x1d6ck0k"></path>
            </g>
        </svg>
        <div style="align-self: center;">
            <b style="font-size: 14px; color: white; font-weight: 600; padding-right: 0px; padding-left: 0px; padding-left: 10px;">{{ $mensajes ? $mensajes->nombre : '' }}</b><br/>
            @if ($mensajes)
                @if ($mensajes->relation_type == 12 || $mensajes->relation_type == 14)
                    @if ($mensajes->relation_module->estado == 0 || $mensajes->relation_module->estado == 3)
                        <span class="badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">Activo</span>
                    @elseif ($mensajes->relation_module->estado == 1)
                        <span class="badge bg-gradient-warning" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">En Proceso</span>
                    @elseif ($mensajes->relation_module->estado == 2)
                        <span class="badge bg-gradient-success" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">Cerrado</span>
                    @endif
                @endif
            @endif
        </div>
    </div>
    <div id="mensaje-body" class="offcanvas-body wrapper" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }}">
        @if ($mensajes)
            @foreach($mensajes->mensajes as $mensaje)
                @if ($mensaje->user_id == $usuario_id)
                    <div class="mensaje-estilo-derecha">
                        @if (count($mensaje->archivos))
                            @foreach ($mensaje->archivos as $archivo)
                                @if (explode('/', $archivo->tipo_archivo)[0] == 'image')
                                    <img class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" alt="Imagen" style="max-height: 250px; max-width: 300px; height: auto; margin-bottom: 10px; margin-left: auto; display: flow;">
                                @elseif (explode('/', $archivo->tipo_archivo)[0] == 'video')
                                    <video class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" controls style="max-height: 250px; height: auto; margin-bottom: 10px; margin-left: auto; display: flow;"></video>
                                @else
                                    <iframe src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" style="width: 100%; height: 250px;"></iframe>
                                @endif
                            @endforeach
                        @endif
                        <p style="font-size: 13px; margin-bottom: 0; text-align: right; font-weight: 600; margin-top: -5px; padding-bottom: 10px; margin-left: 14px;">
                            {!! $mensaje->content !!}
                        </p>
                        <div style="display: flex; float: right;">
                            <p class="formato-fecha-propio">{{ $mensaje->created_at }}</p>
                            @if ($mensaje->status == 1)
                                <i class="fas fa-check icono-status-no_entregado" aria-hidden="true"></i>
                            @elseif ($mensaje->status == 2)
                                <i class="fas fa-check-double icono-status-entregado" aria-hidden="true"></i>
                            @elseif ($mensaje->status == 3)
                                <i class="fas fa-check-double icono-status-leido" aria-hidden="true"></i>
                            @endif
                        </div>
                        <!-- <i class="fas fa-caret-down icono-mensaje-derecha" aria-hidden="true"></i> -->
                    </div>
                @else
                    <div class="mensaje-estilo-izquierda">
                        @if (!$mensaje->usuario)
                            <b class="nombre-usuario">Sin Usuario Asignado</b><br/>        
                        @elseif ($mensaje->usuario->firstname && $mensaje->usuario->lastname)
                            <b class="nombre-usuario">{{ $mensaje->usuario->firstname }} {{ $mensaje->usuario->lastname }}</b><br/>
                        @elseif ($mensaje->usuario->firstname)
                            <b class="nombre-usuario">{{ $mensaje->usuario->firstname }}</b><br/>
                        @else
                            <b class="nombre-usuario">{{ $mensaje->usuario->lastname }}</b><br/>
                        @endif
                        @if (count($mensaje->archivos))
                            @foreach ($mensaje->archivos as $archivo)
                                @if (explode('/', $archivo->tipo_archivo)[0] == 'image')
                                    <img class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" alt="Imagen" style="max-height: 250px; max-width: 300px; height: auto; margin-bottom: 10px; margin-left: auto;">
                                @elseif (explode('/', $archivo->tipo_archivo)[0] == 'video')
                                    <video class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" controls style="max-height: 250px; height: auto; margin-bottom: 10px; margin-left: auto;"></video>
                                @else
                                    <iframe src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" style="width: 100%; height: 250px;"></iframe>
                                @endif
                            @endforeach
                        @endif
                        <p class="texto-mensaje">{!! $mensaje->content !!}</p>
                        <p class="formato-fecha">{{ $mensaje->created_at }}</p>
                        <!-- <i class="fas fa-caret-down icono-mensaje-izquierda" aria-hidden="true"></i> -->
                    </div>
                @endif
            @endforeach
        @endif
    </div>
    <div id="offcanvas-footer-mensajes container-chat" class="offcanvas-footer" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }} padding: 10px; background-color: #202c33; display: flex; box-shadow: -2px -2px 2px rgb(0 0 0 / 20%), 0px 0px 0px rgb(0 0 0 / 10%);">
        <div id="accordion-collapse" class="accordion-item" style="align-content: center;">
            <i id="icon-open-actions" class="fas fa-plus icon-action" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree"></i>
            <i id="icon-close-actions" class="fas fa-times icon-action" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree" style="display: none;"></i>
        </div>
        <input
            id="input-mensaje-chat"
            wire:model="textoEscrito"
            class="form-control-no-upp"
            type="text"
            placeholder="Escribe un mensaje"
            style="background-color: #2a3942; border: 0px; color: #FFF; padding: 10px;"
        >
        <i
            id="button-mensaje-chat"
            wire:click="enviarMensaje"
            class="fas fa-paper-plane icon-send"
        >
        </i>
    </div>
    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
        <div class="container accordion-body">

            <div id="container-actions" style="display: flex; gap: 10px;">
                
                @if ($mensajes)
                    @if ($mensajes->relation_type == 12 || $mensajes->relation_type == 14)
                        @canany(['mensajes pqrsf', 'mensajes turnos'])
                            <div id="button-action-estado-chat" class="button-action-chat">
                                <i class="fas fa-exchange-alt icon-action-chat turquoise"></i>
                                <b style="color: white; font-weight: 400;">Estados</b>
                            </div>
                        @endcanany
                    @endif

                    @if ($mensajes->relation_type == 14)
                        @if ($mensajes->relation_module->id_usuario == $usuario_id)
                            @if ($mensajes->relation_module->estado == 0 || $mensajes->relation_module->estado == 3)
                            <div id="button-action-iniciar-turno" class="button-action-chat butonActionProcesoAction">
                                <i class="fas fa-stopwatch icon-action-chat" style="color: #fb8b40;"></i>
                                <b style="color: white; font-weight: 400;">Iniciar</b>
                            </div>
                            @endif
                            @if ($mensajes->relation_module->estado == 1)
                            <div id="button-action-finalizar-turno" class="button-action-chat butonActionCerradoAction">
                                <i class="fas fa-stop icon-action-chat" style="color: #2dcea3;"></i>
                                <b style="color: white; font-weight: 400;">Finalizar</b>
                            </div>
                            @endif
                        @endif
                    @endif
                @endif
    
                <div id="button-action-imagen-chat" class="button-action-chat">
                    <i class="fas fa-image icon-action-chat springgreen"></i>
                    <b style="color: white; font-weight: 400;">Archivos</b>
                </div>
            </div>

            <div id="container-estados" style="text-align: center;">

                <b class="nombre-container-estados">
                    Acciones
                </b>
                <br/>

                @if ($mensajes)
                    @if ($mensajes->relation_type == 12 || $mensajes->relation_type == 14)
                        @canany(['mensajes pqrsf', 'mensajes turnos'])
                            @if ($mensajes->relation_module->estado != 0 && $mensajes->relation_module->estado != 3)
                                <span id="butonActionActivoAction" href="javascript:void(0)" class="btn badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 12px; margin-right: 5px;">Activo</span>
                            @endif
                            @if ($mensajes->relation_module->estado != 1)
                                <span id="butonActionProcesoAction" href="javascript:void(0)" class="btn badge bg-gradient-warning butonActionProcesoAction" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 12px; margin-right: 5px;">En Proceso</span>
                            @endif
                            @if ($mensajes->relation_module->estado != 2)
                                <span id="butonActionCerradoAction" href="javascript:void(0)" class="btn badge bg-gradient-success butonActionCerradoAction" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 12px; margin-right: 5px;">Cerrado</span>
                            @endif
                        @endcanany
                    @endif
                @endif                
                
            </div>

        </div>
    </div>
    <!-- END MENSAJES -->
</div>