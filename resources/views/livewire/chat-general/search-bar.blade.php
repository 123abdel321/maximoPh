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
