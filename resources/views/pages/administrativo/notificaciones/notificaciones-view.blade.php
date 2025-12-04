<style>

    .dtrg-group {
        font-weight: bold;
        background-color: #f0f0f0;
        padding: 10px;
        text-transform: uppercase;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">
            <div class="card-body row">

                <ul class="nav nav-tabs" role="tablist" style="border-bottom: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="whatsapp-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab" aria-controls="whatsapp" aria-selected="true">
                            Whatsapp
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="email-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">
                            Email
                        </button>
                    </li>
                </ul>
    
                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade" id="whatsapp" role="tabpanel" aria-labelledby="whatsapp_tab">
                        @include('pages.administrativo.notificaciones.notificaciones_whatsapp-table')
                    </div>
                    <div class="tab-pane fade show active" id="email" role="tabpanel" aria-labelledby="email_tab">
                        @include('pages.administrativo.notificaciones.notificaciones_email-table')
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('pages.administrativo.notificaciones.notificaciones_email-detalle')
@include('pages.administrativo.notificaciones.notificaciones_whatsapp-detalle')

<script>
    var tokenEcoNotificaciones = @json($tokenEco);
</script>