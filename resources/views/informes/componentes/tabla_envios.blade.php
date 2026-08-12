<div class="d-flex justify-content-between align-items-center mb-3 px-3">
    <div class="d-flex align-items-center">
        <label for="statusFilter" class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Filtrar por Estado:</label>
        <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
            <option value="">Todos los Estados</option>
            <option value="Accepted">Accepted / Aceptado</option>
            <option value="Wrong Answer">Wrong Answer / Rechazado</option>
            <option value="Error">Error</option>
            <option value="In Process">In Process</option>
        </select>
    </div>
</div>

<div class="table-responsive p-0">
    <table class="table align-items-center mb-0" id="table">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre Completo
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Rut
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                    Lenguaje
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                    Problema
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                    Estado
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                    Casos Resueltos
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
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
                                <h6 class="mb-0 text-sm font-weight-bold">{{ $envio->firstname }} {{$envio->lastname}}</h6>
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
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-secondary">{{ $envio->nombre_lenguaje }}</span>
                    </td>
                    <td>
                        <a href="{{route('problemas.ver', ['id_curso'=>$envio->id_curso, 'codigo'=>$envio->codigo])}}" class="text-sm font-weight-bold text-primary">{{$envio->nombre}}</a>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm @if($envio->solucionado==true) bg-gradient-success @elseif($envio->estado == "Error" || $envio->estado == "Rechazado") bg-gradient-danger @else bg-gradient-warning @endif">{{$envio->solucionado == true? 'Accepted' : ($envio->estado=="Rechazado" || $envio->estado=="Error"? $envio->resultado : "In Process")}}</span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-info" title="Casos Resueltos">{{$envio->cant_casos_resuelto}} / {{$envio->total_casos}}</span>
                    </td>
                    <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-dark" title="Puntaje Obtenido">{{$envio->puntaje}}</span>
                    </td>
                    @canany(['ver informe del problema'])
                        <td class="align-middle text-end">
                            <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                @can('ver informe del problema')
                                    <a class="btn btn-xs btn-outline-info mb-0" title="Ver Detalles del Envío"
                                    href="{{ route('envios.ver', ['token' => $envio->token]) }}"><i class="fa fa-info-circle me-1"></i> Ver Detalles</a>
                                @endcan
                            </div>
                        </td>
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    $('#table').DataTable().column(4).search(this.value).draw();
                }
            });
        }
    });
</script>