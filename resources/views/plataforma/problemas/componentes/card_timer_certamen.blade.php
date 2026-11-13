<div class="card border-danger px-2 py-1 mb-3" style="height:4rem">
    <h5 class="text-center my-3">Tiempo Restante: <strong id="timer">--:--:--</strong></h5>
    <div class="d-flex justify-content-center">
        <form action="{{ route('certamen.finalizar', ['token' => $res_certamen->token]) }}" method="POST"
            id="finalizar_form">
            @csrf
        </form>
    </div>
</div>

@push('js')
    <script>
        @php
            $fechaTerminoEfectivaObj = (isset($res_certamen) && $res_certamen->fecha_termino_efectiva) 
                ? $res_certamen->fecha_termino_efectiva 
                : ((isset($res_certamen) && $res_certamen->certamen) ? \Carbon\Carbon::parse($res_certamen->certamen->fecha_termino) : null);
            $certamenFechaTermino = $fechaTerminoEfectivaObj ? $fechaTerminoEfectivaObj->format('Y-m-d H:i:s') : null;
        @endphp
        const fecha_termino = new Date(@json($certamenFechaTermino));
        var x = setInterval(function() {

            var now = new Date().getTime();

            var distancia = fecha_termino - now;

            var horas = Math.floor((distancia / (1000 * 60 * 60)));
            var minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
            var segundos = Math.floor((distancia % (1000 * 60)) / 1000);
            document.getElementById("timer").innerHTML = ("0" + horas).slice(-2) + ":" + ("0" + minutos).slice(-2) +
                ":" + ("0" + segundos).slice(-2);
            if (distancia < 1800000) {
                document.getElementById("timer").classList.add('text-danger');
            }
            if (distancia < 0) {
                clearInterval(x);
                document.getElementById("timer").innerHTML = "Finalizado";
                if (typeof formSubmited !== 'undefined') {
                    formSubmited = true;
                }
                document.getElementById('finalizar_form').submit();
            }
        }, 1000);
    </script>
@endpush
