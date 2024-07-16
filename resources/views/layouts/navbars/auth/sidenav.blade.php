<style>
    .side-nav-maximo-open{
        width: 100% !important;
    }
    .side-nav-maximo-close{
        width: 0px !important;
    }
    .ocultar {
        display: none;
    }
    #sidenav-main {
        background-color: #000000 !important;
        opacity: 0.85 !important;
    }

    .text-blue {
        -webkit-animation: color_change 2s infinite alternate;
        -moz-animation: color_change 2s infinite alternate;
        -ms-animation: color_change 2s infinite alternate;
        -o-animation: color_change 2s infinite alternate;
        animation: color_change 2s infinite alternate;
    }

    @-webkit-keyframes color_change {
        from { color: skyblue; }
        to { color: darkcyan ; }
    }
    @-moz-keyframes color_change {
        from { color: skyblue; }
        to { color: darkcyan ; }
    }
    @-ms-keyframes color_change {
        from { color: skyblue; }
        to { color: darkcyan ; }
    }
    @-o-keyframes color_change {
        from { color: skyblue; }
        to { color: darkcyan ; }
    }
    @keyframes color_change {
        from { color: skyblue; }
        to { color: darkcyan ; }
    }

</style>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 side-nav-maximo-close" id="sidenav-main" style="z-index: 99 !important; border-radius: 0px 10px 10px 0px;">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" style="color: white !important;" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" target="_blank" style="text-align: -webkit-center; text-align: center;">
            <img src="" id="side_main_logo" alt="main_logo" style="max-height: 3.5rem;"><br/>
        </a>
    </div>
    <br/>
    <hr class="horizontal dark mt-0">

    <ul class="collapse navbar-collapse navbar-nav" id="sidenav-collapse-main" style="height: 100%;">

    @foreach ($menus as $menu)

        <li class="nav-item">
            <div id="nav_{{ $menu[0]->padre->id }}" data-bs-toggle="collapse" href="#collapse{{ $menu[0]->padre->id }}" class="nav-link collapsed nav-padre" aria-controls="{{ $menu[0]->padre->nombre }}" role="button" aria-expanded="false" style="color: white;"
                onclick="closeAnotherItems('{{ $menu[0]->padre->id }}' )">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="{{ $menu[0]->padre->icon }} text-sm opacity-10" style="color: #12d9ff !important;"></i>
                </div>
                <span class="nav-link-text ms-1">{{ $menu[0]->padre->nombre }}</span>
            </div>

            <div class="collapse collapse-slow" id="collapse{{ $menu[0]->padre->id }}" >
                <ul class="navbar-nav" style="margin-left: 15px; border-left: solid 1px #18ff00; margin-left: 30px;">

                    @foreach ($menu as $item)
                        <li class="nav-item tipo_menu_{{ $item->tipo_menu }}">
                            <a class="nav-link button-side-nav" id="sidenav_{{ $item->url }}" onclick="openNewItem('{{ $item->url }}', '{{ $item->nombre }}', '{{ $item->icon }}')" style="margin-left: 20px;">
                                <span class="nav-link-text ms-1">{{ $item->nombre }}</span>
                            </a>
                        </li>
                    @endforeach

                </ul>
            </div>
        </li>

    @endforeach

    </ul>
</aside>

<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl" id="sidenav-main-2" style="z-index: 99 !important; width: 11px; cursor: pointer; background-color: #075260 !important; border-radius: 0px 7px 7px 0px;">
    <span id="button-mostrar-lateral" class="nav-link-text ms-1" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-right" style="color: #FFF;"></i>
    </span>
    <span id="button-ocultar-lateral" class="nav-link-text ms-1 ocultar" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-left"  style="color: #FFF;"></i>
    </span>
</aside>