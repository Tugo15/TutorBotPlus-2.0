<nav class="navbar navbar-expand-lg border-bottom shadow-sm bg-body-tertiary py-2 px-3 px-md-4 position-sticky top-0 start-0 tb-student-navbar" style="width: 100%; z-index:99">
    <div class="container-fluid">
        <a class="navbar-brand me-2 me-sm-3" href="{{ route('cursos.listado') }}">
            <img src="{{ asset('img/ucsc_logo2.png') }}" alt="UCSC Logo" height="60" class="tb-student-logo">
        </a>
        <div class="d-flex align-items-center gap-2 ms-auto order-lg-last">
            <button type="button"
                class="btn btn-sm btn-outline-secondary mb-0 tb-theme-toggle-btn"
                data-theme-toggle
                title="Cambiar a modo oscuro"
                aria-label="Cambiar a modo oscuro"
                aria-pressed="false">
                <i class="fa fa-moon" data-theme-icon aria-hidden="true"></i>
                <span class="d-none d-sm-inline text-xs" data-theme-label>Oscuro</span>
            </button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('cursos.*') || Route::is('problemas.*') ? 'active fw-bold' : '' }}"
                        aria-current="page" href="{{ route('cursos.listado') }}"><i class="fa fa-book-open"></i><span>Cursos</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('certamenes.*') || Route::is('certamen.*') ? 'active fw-bold' : '' }}"
                        href="{{ route('certamenes.listado') }}"><i class="fa fa-clipboard-list"></i><span>Evaluaciones</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('envios.*') ? 'active fw-bold' : '' }}"
                        href="{{ route('envios.listado') }}"><i class="fa fa-paper-plane"></i><span>Envíos</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa fa-user-circle"></i>
                        <span>{{ auth()->user()->username }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @can('acceso al panel de administración')
                            <li><a class="dropdown-item" href="{{ route('home') }}">Ir al Panel de Administración</a>
                            </li>
                        @endcan
                        @auth
                            <li><a class="dropdown-item" href="{{ route('ver.perfil') }}">Mi Perfil</a>
                            </li>
                            <li>
                                <form role="form" method="post" action="{{ route('logout') }}" id="logout-form" class="m-0">
                                    @csrf
                                    <a class="dropdown-item"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        href="{{ route('logout') }}">Cerrar Sesión</a>
                                </form>
                            </li>
                        @endauth
                        @guest
                            <li><a class="dropdown-item" href="{{ route('login') }}">Iniciar Sesión</a></li>
                        @endguest
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
