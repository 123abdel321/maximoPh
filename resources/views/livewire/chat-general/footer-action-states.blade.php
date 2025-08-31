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
