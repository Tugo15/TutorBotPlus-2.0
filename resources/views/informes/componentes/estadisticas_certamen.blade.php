<div class="row mx-3 d-flex align-items-stretch">
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Soluciones Enviadas</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$certamen_estadistica->cantidad_resueltos ?? 0}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Cantidad de Intentos</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$certamen_estadistica->cantidad_intentos ?? 0}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Evaluaciones Aceptadas</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ $certamen_estadistica->cantidad_resueltos ?? 0 }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Promedio Resueltos</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ number_format((float)($certamen_estadistica->promedio_resolucion_problemas ?? 0), 1) }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-12 mb-xl-0 mb-4">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary">Puntaje Promedio</p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ number_format((float)($certamen_estadistica->puntaje_promedio ?? 0), 1) }}
                </h5>
            </div>
        </div>
    </div>
</div>