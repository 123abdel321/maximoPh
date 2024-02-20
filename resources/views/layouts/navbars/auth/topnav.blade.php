<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl
        {{ str_contains(Request::url(), 'virtual-reality') == true ? ' mt-3 mx-3 bg-primary' : '' }}" id="navbarBlur"
        data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <h4 class="font-weight-bolder text-white mb-0" id="titulo-view"></h4>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <!-- <p>EMPRESA NOMBRE</p> -->
            </div>
            <ul class="navbar-nav justify-content-end" style="flex-direction: inherit !important;">

                <li class="nav-item px-2 d-flex align-items-center">
                    <div style="color: aliceblue; text-transform: uppercase; font-size: 16px; font-weight: bold;" id="nombre-usuario-topnav">
                        @if (Auth::user()->firstname)
                            {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                        @else
                            {{ Auth::user()->username }}
                        @endif
                    </div>
                </li>
                
                <li class="nav-item ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenavMaximo">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
                <!-- <li class="nav-item px-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0">
                        <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                    </a>
                </li> -->
                <li class="nav-item px-2 d-flex align-items-center">
                    <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a id="sessionLogout" class="nav-link text-white font-weight-bold px-0" style="cursor: pointer;">
                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
