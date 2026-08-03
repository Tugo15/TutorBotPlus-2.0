<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between">
                    <h6>Gráficas</h6>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="row">
                    @if(!empty($lenguajes_estadistica) || !empty($estadistica_estados))
                        <div class="col @if(empty($estadistica_estados)) d-none @endif">
                            <canvas id="estadistica_estado" height="400"></canvas>
                        </div>
                        <div class="col @if(empty($lenguajes_estadistica)) d-none @endif">
                            <canvas id="lenguajes_estadistica" height="400"></canvas>
                        </div>
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
                    rgb_estados_envios.push('rgb(42,255,0)');
                    break;
                case "Wrong Answer":
                    rgb_estados_envios.push('rgb(255,45,0)');
                    break;
                case "Time Limit Exceeded":
                    rgb_estados_envios.push('rgb(255,251,0)');
                    break;
                case "Compilation Error":
                    rgb_estados_envios.push('rgb(26,82,255)');
                    break;
                case "Internal Error":
                    rgb_estados_envios.push('rgb(255,193,29)')
                    break;
                case "Exec Format Error":
                    rgb_estados_envios.push('rgb(39,246,243)')
                    break;
                default:
                    rgb_estados_envios.push('rgb(255,)' + Math.random() * 255 + ', ' + Math.random() * 255 +')');
            }
        });
        const data_1 = {
            labels: @json(array_keys($estadistica_estados)),
            datasets: [{
                label: 'Estados de los envios',
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
                        text: 'Estados de los envios',
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
        new Chart(
            document.getElementById("estadistica_estado"), config_1
        )
        new Chart(
            document.getElementById("lenguajes_estadistica"), config_2
        )
    </script>
@endpush
