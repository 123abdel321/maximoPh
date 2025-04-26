@if (!$mensaje->usuario)
    <b class="nombre-usuario">Sin Usuario Asignado</b><br/>
@elseif ($mensaje->usuario->firstname && $mensaje->usuario->lastname)
    <b class="nombre-usuario">{{ $mensaje->usuario->firstname }} {{ $mensaje->usuario->lastname }}</b><br/>
@elseif ($mensaje->usuario->firstname)
    <b class="nombre-usuario">{{ $mensaje->usuario->firstname }}</b><br/>
@else
    <b class="nombre-usuario">{{ $mensaje->usuario->lastname }}</b><br/>
@endif
