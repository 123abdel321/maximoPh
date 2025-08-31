<div style="display: flex; float: {{ $mensaje->user_id == $usuario_id ? 'right' : 'left' }};">
    <p class="formato-fecha-propio">{{ $mensaje->created_at }}</p>
    @if ($mensaje->status == 1)
        <i class="fas fa-check icono-status-no_entregado" aria-hidden="true"></i>
    @elseif ($mensaje->status == 2)
        <i class="fas fa-check-double icono-status-entregado" aria-hidden="true"></i>
    @elseif ($mensaje->status == 3)
        <i class="fas fa-check-double icono-status-leido" aria-hidden="true"></i>
    @endif
</div>
