<div class="row mx-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 align-items-stretch">
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Soluciones / Intentos</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$curso_estadistica->sum_cantidad_resueltos."/".$curso_estadistica->sum_cantidad_intentos}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Evaluaciones Aceptadas</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ $curso_estadistica->sum_cantidad_resueltos ?? 0 }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Retroalimentación Solicitada</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$curso_estadistica->sum_cant_ra_solicitada}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Tasa de Éxito</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    @if($curso_estadistica->sum_cantidad_intentos==0)
                    0%
                    @else
                    {{round(($curso_estadistica->sum_cantidad_resueltos/$curso_estadistica->sum_cantidad_intentos)*100)}}%
                    @endif
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Tiempo Prom. por Problema</p>
                <h5 class="font-weight-bolder text-dark mb-0">{{$curso_estadistica->tiempo_promedio}}</h5>
            </div>
        </div>
    </div>
</div>