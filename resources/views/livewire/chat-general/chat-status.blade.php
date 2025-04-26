@if ($mensajes->relation_type == 12 || $mensajes->relation_type == 14)
    @if ($mensajes->relation_module->estado == 0 || $mensajes->relation_module->estado == 3)
        <span class="badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">Activo</span>
    @elseif ($mensajes->relation_module->estado == 1)
        <span class="badge bg-gradient-warning" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">En Proceso</span>
    @elseif ($mensajes->relation_module->estado == 2)
        <span class="badge bg-gradient-success" style="margin-bottom: 0rem !important; min-width: 50px; font-size: 9px; margin-left: 10px;">Cerrado</span>
    @endif
@endif
