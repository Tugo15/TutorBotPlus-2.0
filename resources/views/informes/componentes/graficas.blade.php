<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-0">
                <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                    <div class="d-none d-lg-block" style="flex: 1;"></div>
                    <div class="text-center" style="flex: 2;">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-chart-pie me-2 text-primary"></i>Gráficas</h6>
                        <p class="text-xs text-secondary mb-0">Estadísticas visuales de estados y lenguajes.</p>
                    </div>
                    <div class="d-none d-lg-block" style="flex: 1;"></div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="row">
                    @if(!empty($lenguajes_estadistica) || !empty($estadistica_estados))
                        @if(!empty($estadistica_estados))
                            <div class="col">
                                <canvas id="estadistica_estado" height="400"></canvas>
                            </div>
                        @endif
                        @if(!empty($lenguajes_estadistica))
                            <div class="col">
                                <canvas id="lenguajes_estadistica" height="400"></canvas>
                            </div>
                        @endif
                    @else
                        <div class="col-12 text-center py-5">
                            <i class="fa fa-chart-pie text-muted fa-3x mb-3 opacity-5"></i>
                            <h6 class="text-secondary mb-0">No hay datos disponibles para graficar</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
    <script>
        const cant_lenguajes = @json(sizeof($lenguajes_estadistica));
        const estadistica_estado_key = @json(array_keys($estadistica_estados));
        var rgb_lenguajes = []
        var rgb_estados_envios = []
        for(var i=0; i<cant_lenguajes; i++){
            rgb_lenguajes.push('rgb(' + Math.random() * 255 + ', ' + Math.random() * 255 + ', ' + Math.random() * 255 +')')
        }
        estadistica_estado_key.forEach(element => {
            switch(element){
                case "Accepted":
                case "Aceptado":
                    rgb_estados_envios.push('rgb(42,255,0)');
                    break;
                case "Aceptado (Variación de Formato)":
                    rgb_estados_envios.push('rgb(45,206,137)');
                    break;
                case "Wrong Answer":
                case "Respuesta Incorrecta":
                case "Rechazado":
                    rgb_estados_envios.push('rgb(255,45,0)');
                    break;
                case "Time Limit Exceeded":
                case "Tiempo Límite Excedido":
                    rgb_estados_envios.push('rgb(255,251,0)');
                    break;
                case "Compilation Error":
                case "Error de Compilación":
                    rgb_estados_envios.push('rgb(26,82,255)');
                    break;
                case "Internal Error":
                case "Error Interno":
                case "Error de Ejecución":
                    rgb_estados_envios.push('rgb(255,193,29)')
                    break;
                case "Exec Format Error":
                    rgb_estados_envios.push('rgb(39,246,243)')
                    break;
                default:
                    rgb_estados_envios.push('rgb(255, ' + Math.random() * 255 + ', ' + Math.random() * 255 +')');
            }
        });
        const data_1 = {
            labels: @json(array_keys($estadistica_estados)),
            datasets: [{
                label: 'Estados de los envíos',
                data: @json(array_values($estadistica_estados)),
                backgroundColor: rgb_estados_envios,
            }]
        }
        const data_2 = {
            labels: @json(array_keys($lenguajes_estadistica)),
            datasets: [{
                label: 'Lenguajes de Programación utilizados',
                data: @json(array_values($lenguajes_estadistica)),
                backgroundColor: rgb_lenguajes,
            }]
        }
        const config_1 = {
            type: 'doughnut',
            data: data_1,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Estados de los envíos',
                        color: 'navy',
                        position: 'bottom',
                        align: 'center',
                        font: {
                            weight: 'bold'
                        },
                        padding: 8,
                        fullSize: true,
                    }
                }
            },
        };
        const config_2 = {
            type: 'doughnut',
            data: data_2,

            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Lenguajes de Programación utilizados',
                        color: 'navy',
                        position: 'bottom',
                        align: 'center',
                        font: {
                            weight: 'bold'
                        },
                        padding: 8,
                        fullSize: true,
                    }
                }
            },
        };
        if (document.getElementById("estadistica_estado")) {
            new Chart(
                document.getElementById("estadistica_estado"), config_1
            );
        }
        if (document.getElementById("lenguajes_estadistica")) {
            new Chart(
                document.getElementById("lenguajes_estadistica"), config_2
            );
        }
    </script>
@endpush
