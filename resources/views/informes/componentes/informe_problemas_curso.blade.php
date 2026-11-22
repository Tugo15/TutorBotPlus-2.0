<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-0">
                <div class="text-center pb-2">
                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-chart-line me-2 text-primary"></i>Estadísticas de Problemas del Curso</h6>
                </div>
            </div>
            <div class="card-body px-3 px-md-4 pt-3 pb-3">
                <div class="row g-2">
                    @if ((!empty($lenguajes_estadistica) || !empty($estadistica_estados)) && isset($problema_mas_resuelto) && isset($problema_mas_intentado))
                        <div class="col-12 col-lg-6">
                            <div class="py-2 px-2 bg-gray-100 rounded border d-flex align-items-center justify-content-center flex-wrap gap-1 h-100" style="font-size: 0.72rem;">
                                <span class="text-dark"><i class="fa fa-check-circle text-success me-1"></i><strong>Problema más resuelto:</strong></span>
                                <a href="{{ route('problemas.ver', ['id_curso' => $curso_estadistica->id ?? '', 'codigo' => $problema_mas_resuelto->codigo]) }}"
                                    class="text-primary font-weight-bold mb-0">{{ $problema_mas_resuelto->nombre }} ({{ $problema_mas_resuelto->cantidad_resueltos }} soluciones y {{ $problema_mas_resuelto->cantidad_intentos }} intentos)</a>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="py-2 px-2 bg-gray-100 rounded border d-flex align-items-center justify-content-center flex-wrap gap-1 h-100" style="font-size: 0.72rem;">
                                <span class="text-dark"><i class="fa fa-fire text-warning me-1"></i><strong>Problema con más intentos:</strong></span>
                                <a href="{{ route('problemas.ver', ['id_curso' => $curso_estadistica->id ?? '', 'codigo' => $problema_mas_intentado->codigo]) }}"
                                    class="text-primary font-weight-bold mb-0">{{ $problema_mas_intentado->nombre }} ({{ $problema_mas_intentado->cantidad_resueltos }} soluciones y {{ $problema_mas_intentado->cantidad_intentos }} intentos)</a>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div style="position: relative; height: 300px;">
                                <canvas id="grafica_problemas"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="col-12 text-center py-4">
                            <i class="fa fa-chart-bar text-muted opacity-5 fa-3x mb-3"></i>
                            <h6 class="text-secondary mb-0">No hay datos disponibles para graficar</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    @once('chartjs')
        <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
    @endonce
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
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        };

        var barChart = grafica_problemas ? new Chart(grafica_problemas, {
            type: 'bar',
            data: problemasData,
            options: chartOptions
        }) : null;
    </script>
@endpush
