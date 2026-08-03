<div class="row mx-3">
    <div class="col-xl-2 col-sm-5 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Cantidad de Soluciones Enviadas</p>
                            <h5 class="font-weight-bolder">
                                {{$certamen_estadistica->cantidad_resueltos}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-5 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Cantidad de Intentos</p>
                            <h5 class="font-weight-bolder">
                                {{$certamen_estadistica->cantidad_intentos}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-5 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Evaluaciones Aceptadas</p>
                            <h5 class="font-weight-bolder">
                                @if(isset($estadistica_estados["Accepted"]))
                                    {{ $estadistica_estados["Accepted"]}}
                                @else
                                    0
                                @endif
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Promedio de Resolución de Problemas</p>
                            <h5 class="font-weight-bolder">
                                {{round($certamen_estadistica->promedio_resolucion_problemas)}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-5 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Puntaje Promedio</p>
                            <h5 class="font-weight-bolder">
                                {{round($certamen_estadistica->puntaje_promedio)}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>