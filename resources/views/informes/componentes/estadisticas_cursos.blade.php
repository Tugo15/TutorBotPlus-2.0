<div class="row mx-3">
    <div class="col-xl-2 col-sm-5 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Cantidad de Soluciones/Intentos</p>
                            <h5 class="font-weight-bolder">
                                {{$curso_estadistica->sum_cantidad_resueltos."/".$curso_estadistica->sum_cantidad_intentos}}
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
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Cant. Solicitud de Retroalimentación</p>
                            <h5 class="font-weight-bolder">
                                {{$curso_estadistica->sum_cant_ra_solicitada}}
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
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Tasa de Exito en los problemas</p>
                            <h5 class="font-weight-bolder">
                                @if($curso_estadistica->sum_cantidad_intentos==0)
                                0
                                @else
                                {{round(($curso_estadistica->sum_cantidad_resueltos/$curso_estadistica->sum_cantidad_intentos)*100)}}%
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
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Tiempo Promedio por Problema</p>
                            <h5 class="font-weight-bolder">{{$curso_estadistica->tiempo_promedio}}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>