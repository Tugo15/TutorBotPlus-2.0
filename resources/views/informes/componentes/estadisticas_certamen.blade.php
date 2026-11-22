<div class="row mx-4 px-1 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 align-items-stretch">
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-paper-plane text-primary opacity-10"></i><span>Soluciones Enviadas</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$certamen_estadistica->cantidad_resueltos ?? 0}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-code text-danger opacity-10"></i><span>Cantidad de Intentos</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{$certamen_estadistica->cantidad_intentos ?? 0}}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-check-circle text-success opacity-10"></i><span>Tasa de Éxito</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    @php
                        $certIntentos = (int)($certamen_estadistica->cantidad_intentos ?? 0);
                        $certResueltos = (int)($certamen_estadistica->cantidad_resueltos ?? 0);
                    @endphp
                    {{ $certIntentos > 0 ? round(($certResueltos / $certIntentos) * 100) : 0 }}%
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-list-alt text-info opacity-10"></i><span>Promedio Resueltos</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ number_format((float)($certamen_estadistica->promedio_resolucion_problemas ?? 0), 1) }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <p class="text-xs mb-2 text-uppercase font-weight-bold text-secondary tb-metric-title">
                    <i class="fa fa-trophy text-warning opacity-10"></i><span>Puntaje Promedio</span>
                </p>
                <h5 class="font-weight-bolder text-dark mb-0">
                    {{ number_format((float)($certamen_estadistica->puntaje_promedio ?? 0), 1) }}
                </h5>
            </div>
        </div>
    </div>
</div>