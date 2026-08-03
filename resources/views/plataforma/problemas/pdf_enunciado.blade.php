<!DOCTYPE html>
<html>

<head>
    <title>{{ $problema->nombre }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        #table_informacion {
            width: 65%
        }

        #table_informacion,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding-left: 10px;
            padding-top: 3px;
            padding-bottom: 3px;
        }

        td {
            padding-left: 6px;
        }

        #td_nombre {
            width: 120px;
        }

        td,
        p {
            font-size: 12px;
        }

        hr {
            margin-top: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <h3>Problema - {{ $problema->nombre }}</h3>
    <div>
        <table id="table_informacion">
            <tr>
                <td id="td_nombre">
                    <h5>Puntos</h5>
                </td>
                <td>
                    <p>{{ $problema->puntaje_total }}</p>
                </td>
            <tr>
                <td id="td_nombre">
                    <h5>Límite de Tiempo</h5>
                </td>
                <td>
                    <p>{{ $problema->tiempo_limite ? $problema->tiempo_limite . ' Segundos' : 'No Definido' }}</p>
                </td>
            <tr>
                <td id="td_nombre">
                    <h5>Límite de Memoria</h5>
                </td>
                <td>
                    <p>{{ $problema->memoria_limite ? $problema->memoria_limite . ' KB' : 'No Definido' }}</p>
                </td>
            <tr>
                <td id="td_nombre">
                    <h5>Categorías</h5>
                </td>
                <td>
                    <p>{{ implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray()) }}</p>
                </td>
            <tr>
                <td id="td_nombre">
                    <h5>Lenguajes</h5>
                </td>
                <td>
                    <p>{{ implode(', ', $problema->lenguajes()->get()->pluck('abreviatura')->toArray()) }}</p>
                </td>
            </tr>
        </table>
    </div>
    <hr style="margin-top: 5px; margin-bottom:5px;">
    <h3>Enunciado</h3>
    {!! Str::markdown($problema->body_problema) !!}
</body>

</html>
