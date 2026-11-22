<div class="row mx-4 px-1 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 align-items-stretch">
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-code text-danger opacity-10"></i><span>Soluciones / Intentos</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$problema_estadistica->cantidad_resueltos."/".$problema_estadistica->cantidad_intentos}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-check-circle text-success opacity-10"></i><span>Evaluaciones Aceptadas</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ $problema_estadistica->cantidad_resueltos ?? 0 }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-robot text-primary opacity-10"></i><span>Retroalimentación Solicitada</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$problema_estadistica->cant_retroalimentacion_solicitada}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-calendar-check text-info opacity-10"></i><span>Tasa de Éxito</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    @if($problema_estadistica->cantidad_intentos==0)
                    0%
                    @else
                    {{round(($problema_estadistica->cantidad_resueltos/$problema_estadistica->cantidad_intentos)*100)}}%
                    @endif
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-clock text-warning opacity-10"></i><span>Tiempo Promedio</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">{{$problema_estadistica->tiempo_promedio}}</h5>
            </div>
        </div>
    </div>
</div>