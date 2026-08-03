<div class="table-responsive p-0">
    <table class="table align-items-center mb-0" id="table">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre Completo
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Rut
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Lenguaje
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Problema
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Estado
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Casos Resueltos
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Puntaje
                </th>
                @canany(['ver informe del problema'])
                    <th
                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                        Acción</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($envios as $envio)
                <tr>
                    <td>
                        <div class="d-flex px-3 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $envio->firstname }} {{$envio->lastname}}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex px-3 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $envio->rut }}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex px-3 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $envio->nombre_lenguaje }}</h6>
                            </div>
                        </div>
                    </td>
                    <td><a href="{{route('problemas.ver', ['id_curso'=>$envio->id_curso, 'codigo'=>$envio->codigo])}}">{{$envio->nombre}}</a></td>
                    <td><span class="badge @if($envio->solucionado==true) bg-gradient-success @elseif($envio->estado == "Error" || $envio->estado == "Rechazado") bg-gradient-danger @else bg-gradient-warning @endif">{{$envio->solucionado == true? 'Accepted' : ($envio->estado=="Rechazado" || $envio->estado=="Error"? $envio->resultado : "In Process")}}</span></td>
                    <td>{{$envio->cant_casos_resuelto}} de {{$envio->total_casos}}</td>
                    <td>{{$envio->puntaje}}</td>
                    @canany(['ver informe del problema'])
                        <td class="align-middle text-end">
                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                @can('ver informe del problema')
                                    <a class="btn btn-outline-warning"
                                    href="{{ route('envios.ver', ['token' => $envio->token]) }}">Ver</a>
                                @endcan
                            </div>
                        </td>
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>