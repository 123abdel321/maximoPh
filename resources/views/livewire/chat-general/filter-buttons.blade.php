<div>
    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
        <label wire:click="agregarFiltroTypo(0)" class="btn-filter-chat-types btn btn-sm btn-outline-primary" for="btnradio1">Todos</label>

        <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
        <label wire:click="agregarFiltroTypo(12)" class="btn-filter-chat-types btn btn-sm btn-outline-primary" for="btnradio2">Pqrsf</label>

        <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
        <label wire:click="agregarFiltroTypo(14)" class="btn-filter-chat-types btn btn-sm btn-outline-primary" for="btnradio3">Turnos</label>

        <input type="radio" class="btn-check" name="btnradio" id="btnradio4" autocomplete="off">
        <label wire:click="agregarFiltroTypo(16)" class="btn-filter-chat-types btn btn-sm btn-outline-primary" for="btnradio4">Novedades</label>
    </div>
</div>
