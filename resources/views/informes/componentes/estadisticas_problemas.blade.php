<div class="row mx-3 d-flex align-items-stretch">
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Soluciones / Intentos</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$problema_estadistica->cantidad_resueltos."/".$problema_estadistica->cantidad_intentos}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Evaluaciones Aceptadas</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    @if(isset($estadistica_estados["Accepted"]))
                        {{ $estadistica_estados["Accepted"]}}
                    @else
                        0
                    @endif
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Retroalimentación Solicitada</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$problema_estadistica->cant_retroalimentacion_solicitada}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Tasa de Éxito</p>
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
    <div class="col-xl-3 col-sm-12 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Tiempo Promedio</p>
                <h5 class="font-weight-bolder text-dark mb-0">{{$problema_estadistica->tiempo_promedio}}</h5>
            </div>
        </div>
    </div>
</div>