<style>
    .add-menu {
        z-index: 1;
        height: 100px;
        width: 100px;
        cursor: pointer;
        transition: 0.2s;
        border-radius: 10px;
        border: dashed white;
        text-align-last: center;
        background-color: #f0f8ff42;
        margin-bottom: 10px;
    }

    .menu-primary {
        z-index: 1;
        height: 100px;
        width: 100px;
        cursor: pointer;
        transition: 0.2s;
        border-radius: 10px;
        border: white;
        text-align-last: center;
        background-color: #184a58;
        margin-bottom: 10px;
        border-style: outset;
        transition-duration: 0.5s;
        align-content: center;
        margin-left: 20px;
    }

    .menu-primary:hover {
        background-color: #1a616e;
        border-style: solid;
    }

    .add-menu:hover {
        background-color: #f0f8ffad;
    }

    .text-menu {
        width: 140%;
        margin-left: -14px;
        color: beige;
        text-align: -webkit-center;
        line-height: normal;
        font-size: 15px;
    }

    .icon-menu-carta {
        margin-top: 10px;
        margin-bottom: 10px;
        color: white;
        font-size: 20px !important;
    }
    
</style>
<div class="container-fluid py-2 p-5">
    <div id="menu-propietarios"  class="row" style="display: none;">

        <div class="col-6 col-xl-6 col-lg-4 col-sm-3 col-md-2 menu-primary" onclick="openNewItem('estadocuenta', 'Estado de cuenta', 'fas fa-poll-h')">
            <i class="fas fa-file-invoice-dollar icon-menu-carta"></i>
            <p class="text-menu">
                FACTURA
            </p>
        </div>

        <div class="col-6 col-xl-6 col-lg-4 col-sm-3 col-md-2 menu-primary" onclick="openNewItem('pqrsf', 'PQRSF', 'fas fa-comments')">
            <i class="fas fa-table icon-menu-carta"></i>
            <p class="text-menu">
                PQRSF
            </p>
        </div>

        <div class="col-6 col-xl-6 col-lg-4 col-sm-3 col-md-2 menu-primary" onclick="openNewItem('porteria', 'Porteria', 'fas fa-user-shield')">
            <i class="fas fa-user-shield icon-menu-carta"></i>
            <p class="text-menu">
                PORTERIA
            </p>
        </div>

    </div>
</div>