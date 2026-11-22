<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-2 mx-md-4 shadow-none border-radius-xl" id="navbarBlur"
    data-scroll="false">
    <div class="container-fluid py-1 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <nav aria-label="breadcrumb" style="min-width: 0; max-width: calc(100% - 210px);">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-2 flex-nowrap">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="{{route('home')}}">Inicio</a></li>
                @if(isset($title) && mb_strtolower(trim($title)) !== 'inicio')
                    <li class="breadcrumb-item text-sm text-white active text-truncate" style="max-width: min(420px, 38vw);" aria-current="page">{{ $title }}</li>
                @else
                    <li class="breadcrumb-item text-sm text-white active" aria-current="page">Panel Principal</li>
                @endif
            </ol>
            <h6 class="font-weight-bolder text-white mb-0 text-truncate" style="max-width: min(460px, 42vw);">{{ $title }}</h6>
        </nav>
        <div class="d-flex align-items-center" id="navbar">
            <ul class="navbar-nav d-flex flex-row align-items-center justify-content-end gap-2 mb-0">
                <li class="nav-item d-none d-sm-flex align-items-center me-2">
                    <span class="text-white font-weight-bold text-sm opacity-9">
                        <i class="fa fa-user-circle me-1"></i> {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}
                    </span>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <button type="button"
                        class="btn btn-link nav-link text-white font-weight-bold px-2 mb-0 tb-theme-toggle-btn"
                        data-theme-toggle
                        title="Cambiar a modo oscuro"
                        aria-label="Cambiar a modo oscuro"
                        aria-pressed="false">
                        <i class="fa fa-moon me-1" data-theme-icon aria-hidden="true"></i>
                        <span class="d-none d-md-inline text-xs" data-theme-label>Oscuro</span>
                    </button>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="nav-link text-white font-weight-bold px-1" title="Cerrar Sesión">
                            <i class="fa fa-sign-out-alt me-sm-1"></i>
                            <span class="d-none d-sm-inline">Cerrar Sesión</span>
                        </a>
                    </form>
                </li>
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0 cursor-pointer" id="iconNavbarSidenav" title="Alternar menú">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
