<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between">
                    <h6>Estadisticas de Problemas del Curso</h6>
                </div>
            </div>
            <div class="card-body px-5 pt-0 pb-2">
                <div class="row">
                    @if (!empty($lenguajes_estadistica) || !empty($estadistica_estados))
                        <span><strong>Problema más resuelto:</strong></span>
                        <a href="{{ route('problemas.ver', ['id_curso' => $curso_estadistica->id, 'codigo' => $problema_mas_resuelto->codigo]) }}"
                            class="mb-3">{{ $problema_mas_resuelto->nombre }}
                            ({{ $problema_mas_resuelto->cantidad_resueltos }} soluciones y
                            {{ $problema_mas_resuelto->cantidad_intentos }} intentos)</a>
                        <span><strong>Problema con más intentos: </strong></span>
                        <a href="{{ route('problemas.ver', ['id_curso' => $curso_estadistica->id, 'codigo' => $problema_mas_intentado->codigo]) }}"
                            class="mb-3">{{ $problema_mas_intentado->nombre }}
                            ({{ $problema_mas_intentado->cantidad_resueltos }} soluciones y
                            {{ $problema_mas_intentado->cantidad_intentos }} intentos)</a>
                        <canvas id="grafica_problemas" width="300" height="100"></canvas>
                    @else
                        <h6 class="ms-4">No hay datos disponibles para graficar</h6>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
    <script>
        var grafica_problemas = document.getElementById("grafica_problemas");
        const datos_cantidad_resueltos = @json($dataset_problemas->pluck('cantidad_resueltos')->toArray());
        const datos_cantidad_intentos = @json($dataset_problemas->pluck('cantidad_intentos')->toArray());
        const labels = @json($dataset_problemas->pluck('codigo')->toArray());
        const datos_cantidad_retroalimentacion = @json($dataset_problemas->pluck('cant_retroalimentacion_solicitada')->toArray());

        var cantidadRetroalimentacionSolicitada = {
            label: 'Cantidad de Retroalimentación Solicitada',
            data: datos_cantidad_retroalimentacion,
            backgroundColor: 'rgba(255, 91, 123, 0.6)',
            borderColor: 'rgba(255, 91, 123, 1)',
        };
        var cantidadResueltos = {
            label: 'Cantidad de Soluciones',
            data: datos_cantidad_resueltos,
            backgroundColor: 'rgba(0, 255, 119, 0.6)',
            borderColor: 'rgba(0, 255, 119, 1)',
        };
        var cantidadIntentos = {
            label: 'Cantidad de Intentos',
            data: datos_cantidad_intentos,
            backgroundColor: 'rgba(255, 0, 0, 0.6)',
            borderColor: 'rgba(255, 0, 0, 1)',
        };

        var problemasData = {
            labels: labels,
            datasets: [cantidadResueltos, cantidadIntentos, cantidadRetroalimentacionSolicitada]
        };

        var chartOptions = {
            scales: {
                xAxes: [{
                    barPercentage: 1,
                    categoryPercentage: 0.6
                }],
            }
        };

        var barChart = new Chart(grafica_problemas, {
            type: 'bar',
            data: problemasData,
            options: chartOptions
        });
    </script>
@endpush
