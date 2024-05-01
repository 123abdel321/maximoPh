<div class="container-fluid py-2">
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"> -->
<style>
    .swiper-slide {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 18px;
      font-size: 22px;
      font-weight: bold;
      color: #fff;
    }

    .width-500{
        width: 500px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
    }

	.button-add-img {
		background-color: #c9d3e2;
		height: 100%;
		width: 100%;
		cursor: pointer;
		border-radius: 8px;
	}

	.button-add-img-select {
		background-color: #c1f2fb;
		height: 100%;
		width: 100%;
		cursor: pointer;
		border-radius: 8px;
		color: #075260;
	}

	.button-add-img:hover {
		background-color: #075260;
		color: white;
	}

	.button-add-img-select:hover {
		background-color: #075260;
		color: white;
	}

	.button-send-pqrsf {
		background-color: #075260;
		color: white;
		height: 100%;
		width: 100%;
		cursor: pointer;
		border-radius: 8px;
	}

	.button-send-pqrsf:hover {
		background-color: #1691a7;
		color: white;
	}

    .icono-mensaje-derecha {
        color: #33a5bb;
        font-size: 25px;
        margin-left: auto;
        float: inline-end;
        margin-top: -2px;
        margin-right: -8px;
    }

    .icono-mensaje-izquierda {
        color: #767676;
        font-size: 25px;
        margin-left: -10px;
        float: inline-start;
        margin-top: -2px;
    }

    .mensaje-estilo-derecha {
        background-color: #33a5bb;
        border-radius: 10px 10px 10px 10px;
        padding: 10px 13px 10px 15px;
        color: white;
        margin-top: 10px;
        margin-left: auto;
        max-width: fit-content;
    }

    .mensaje-estilo-izquierda {
        background-color: #767676;
        border-radius: 10px 10px 10px 10px;
        padding: 10px 13px 10px 15px;
        color: white;
        margin-top: 10px;
        max-width: fit-content;
    }

  </style>
    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('pqrsf create')
                    <button type="button" class="btn btn-primary btn-sm" id="generatePqrsfNuevo">
                        Agregar pqrsf
                    </button>
                @endcan
				<button id="button-open-datelle-pqrsf" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" style="display: none;"></button>
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputPqrsf" class="form-control form-control-sm search-table" onkeydown="searchPqrsf(event)" placeholder="Buscar">
            </div>
        </div>

        <div id="items-tabla-empresa" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.administrativo.pqrsf.pqrsf-table')

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

        <!-- <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script> -->

        @include('pages.administrativo.pqrsf.pqrsf-form', ['usuario_empresa' => $usuario_empresa])
        @include('pages.administrativo.pqrsf.pqrsf-canv')

    </div>
</div>

<script>
    var id_usuario_logeado = '<?php echo auth()->user()->id; ?>';
</script>