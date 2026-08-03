<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
    id="sidenav-main" data-color="primary">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank">
            <img src="{{ asset('img/ucsc_logo.png') }}" class="navbar-brand-img h-100 ms-2" alt="main_logo">
            <span class="ms-1 font-weight-bold">Tutorbot+</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}"
                    href="{{ route('home') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Inicio</span>
                </a>
            </li>
            @canany(['ver usuario', 'ver rol'])
                <li class="nav-item mt-3 d-flex align-items-center">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Usuarios</h6>
                </li>
            @endcanany
            @can('ver usuario')
                <li class="nav-item">
                    <a class="nav-link {{ str_contains(request()->url(), 'usuarios') == true ? 'active' : '' }}"
                        href="{{ route('usuarios.index') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Usuarios</span>
                    </a>
                </li>
            @endcan
            @can('ver rol')
                <li class="nav-item">
                    <a class="nav-link {{ str_contains(request()->url(), 'roles') == true ? 'active' : '' }}"
                        href="{{ route('roles.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Roles</span>
                    </a>
                </li>
            @endcan
            @canany(['ver curso', 'ver informe del curso'])
                <li class="nav-item mt-3 d-flex align-items-center">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Cursos</h6>
                </li>
            @endcanany
            @canany(['ver curso', 'ver informe del curso'])
                <li class="nav-item">
                    <a class="nav-link {{ str_contains(request()->url(), 'cursos') == true ? 'active' : '' }}"
                        href="{{ route('cursos.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Cursos</span>
                    </a>
                </li>
            @endcan
            @canany(['ver lenguaje de programación'])
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Juez Virtual</h6>
            </li>
            @endcanany
            @can('ver lenguaje de programación')
                <li class="nav-item">
                    <a class="nav-link {{ str_contains(request()->url(), 'lenguajes_programacion') == true ? 'active' : '' }}"
                        href="{{ route('lenguaje_programacion.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Lenguajes de Programación</span>
                    </a>
                </li>
            @endcan
            @canany(['ver categoría de problema', 'ver problemas'])
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Problemas</h6>
            </li>
            @endcanany
            @canany(['ver problemas', 'ver informe del problema'])
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with(Route::currentRouteName(), 'problemas') == true ? 'active' : '' }}"
                        href="{{ route('problemas.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Problemas</span>
                    </a>
                </li>
            @endcan
            @can('ver categoría de problema')
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with(Route::currentRouteName(), 'categorias') == true ? 'active' : '' }}"
                        href="{{ route('categorias.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Categorías</span>
                    </a>
                </li>
            @endcan
            @canany(['ver certamen'])
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Evaluaciones</h6>
            </li>
            @endcanany
            @can('ver certamen')
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with(Route::currentRouteName(), 'certamen') == true ? 'active' : '' }}"
                        href="{{ route('certamen.index') }}">
                        <div
                            class="icon border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa fa-gear" style="color:black;"></i>
                        </div>
                        <span class="nav-link-text ms-1">Evaluaciones</span>
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <a class="nav-link"
                    href="{{ route('cursos.listado') }}">
                    <span class="nav-link-text ms-1">Volver a la Landing Page</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
