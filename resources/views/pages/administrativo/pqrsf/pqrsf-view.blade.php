<div class="container-fluid py-2">

    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-12 col-sm-12">
                @can('pqrsf create')
                    <button type="button" class="btn btn-primary btn-sm" id="generatePqrsfNuevo">
                        Agregar pqrsf
                    </button>
                @endcan

                @can('pqrsf email')
                    <button type="button" class="btn btn-info btn-sm" id="generateEmailNuevo">
                        Redactar email
                    </button>
                @endcan

                <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadPqrsf">
                    <i id="reloadPqrsfIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadPqrsfIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>
            </div>
            <!-- <div class="col-12 col-md-6 col-sm-6">
                <input type="text" id="searchInputPqrsf" class="form-control form-control-sm search-table" onkeydown="searchPqrsf(event)" placeholder="Buscar">
            </div> -->
        </div>

        <div id="items-tabla-pqrsf" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.administrativo.pqrsf.pqrsf-table', ['usuario_empresa' => $usuario_empresa])

                <!-- <div class="swiper mySwiper swiper-flip swiper-3d swiper-initialized swiper-horizontal swiper-watch-progress">
                    <div class="swiper-wrapper" id="swiper-wrapper-730a983e14310fcd9" aria-live="polite" style="cursor: grab;">
                        <div class="swiper-slide swiper-slide-visible swiper-slide-fully-visible swiper-slide-active" role="group" aria-label="1 / 9" style="width: 240px; z-index: 9; transform: translate3d(0px, 0px, 0px) rotateZ(0deg) scale(1);">
							<img style="width: 80px; height: 80px; object-fit: cover;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/imagen/empresa/fondo_imagen__662d0bcd2e985.jpeg">
							<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
							</div>
						</div>
                        <div class="swiper-slide swiper-slide-next" role="group" aria-label="2 / 9" style="width: 240px; z-index: 8; transform: translate3d(calc(7.25% - 240px), 0px, -100px) rotateZ(2deg) scale(1);">
							Slide 2
							<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div>
						</div>
                        <div class="swiper-slide" role="group" aria-label="3 / 9" style="width: 240px; z-index: 7; transform: translate3d(calc(13% - 480px), 0px, -200px) rotateZ(4deg) scale(1);">
							Slide 3
							<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div>
						</div>
                        <div class="swiper-slide" role="group" aria-label="4 / 9" style="width: 240px; z-index: 6; transform: translate3d(calc(17.25% - 720px), 0px, -300px) rotateZ(6deg) scale(1);">Slide 4<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                        <div class="swiper-slide" role="group" aria-label="5 / 9" style="width: 240px; z-index: 5; transform: translate3d(calc(20% - 960px), 0px, -400px) rotateZ(8deg) scale(1);">Slide 5<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                        <div class="swiper-slide" role="group" aria-label="6 / 9" style="width: 240px; z-index: 4; transform: translate3d(calc(20% - 1200px), 0px, -400px) rotateZ(8deg) scale(1);">Slide 6<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                        <div class="swiper-slide" role="group" aria-label="7 / 9" style="width: 240px; z-index: 3; transform: translate3d(calc(20% - 1440px), 0px, -400px) rotateZ(8deg) scale(1);">Slide 7<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                        <div class="swiper-slide" role="group" aria-label="8 / 9" style="width: 240px; z-index: 2; transform: translate3d(calc(20% - 1680px), 0px, -400px) rotateZ(8deg) scale(1);">Slide 8<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                        <div class="swiper-slide" role="group" aria-label="9 / 9" style="width: 240px; z-index: 1; transform: translate3d(calc(20% - 1920px), 0px, -400px) rotateZ(8deg) scale(1);">Slide 9<div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 1;"></div></div>
                    </div>
                    <div class="swiper-pagination swiper-pagination-bullets swiper-pagination-horizontal"><span class="swiper-pagination-bullet swiper-pagination-bullet-active" aria-current="true"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span></div>
                    <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                </div> -->
            </div>
        </div>

        @include('pages.administrativo.pqrsf.email-form')
        @include('pages.administrativo.pqrsf.pqrsf-form', ['usuario_empresa' => $usuario_empresa])

    </div>
</div>