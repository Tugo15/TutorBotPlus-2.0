<!DOCTYPE html>
<html>

<head>
    <title>{{ $problema->nombre }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        #table_informacion {
            width: 100%;
            margin-bottom: 12px;
        }

        #table_informacion,
        th,
        td {
            border: 1px solid #999;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px 8px;
            font-size: 11px;
        }

        #td_nombre {
            width: 130px;
            background-color: #f7f7f7;
        }

        h3 {
            font-size: 15px;
            color: #111;
            margin-top: 10px;
            margin-bottom: 8px;
            page-break-after: avoid;
        }

        h4 {
            font-size: 13px;
            color: #222;
            margin-top: 8px;
            margin-bottom: 6px;
            page-break-after: avoid;
        }

        h5 {
            font-size: 11px;
            margin: 0;
        }

        p {
            font-size: 11px;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        ul, ol {
            margin-top: 4px;
            margin-bottom: 8px;
            padding-left: 20px;
            font-size: 11px;
        }

        li {
            margin-bottom: 3px;
        }

        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        /* Adaptación estricta de imágenes dentro del margen de la hoja */
        img {
            max-width: 100% !important;
            height: auto !important;
            display: block;
            margin: 10px auto;
            page-break-inside: avoid;
        }

        pre, code {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 10.5px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        pre {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 4px;
            margin-top: 4px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .ejemplo-card {
            margin-bottom: 12px;
            border: 1px solid #ddd;
            padding: 8px 10px;
            border-radius: 4px;
            background-color: #fafafa;
            page-break-inside: avoid;
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
                    <p>{{ implode(', ', $problema->lenguajes()->get()->pluck('abreviatura')->map(fn($a) => strtoupper($a))->unique()->toArray()) }}</p>
                </td>
            </tr>
        </table>
    </div>
    <hr style="margin-top: 5px; margin-bottom:5px;">
    @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
        <div style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 8px 12px; border-radius: 4px; margin-bottom: 12px; font-size: 11px;">
            <strong>Restricción:</strong> {{ $problema->restricciones }}
        </div>
    @endif
    <h3>Enunciado</h3>
    @php
        $bodySanitized = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $problema->body_problema ?? '');
        $renderedHtml = Str::markdown($bodySanitized);
        $renderedHtml = str_replace('src="/storage/', 'src="' . public_path('storage/') . '/', $renderedHtml);
    @endphp
    {!! $renderedHtml !!}

    @php
        $casos_ejemplo = $problema->casos_de_prueba()->where('ejemplo', true)->orderBy('id', 'asc')->get();
    @endphp

    @if($casos_ejemplo->count() > 0)
        <h3 style="margin-top: 20px;">Ejemplos de Entrada y Salida</h3>
        @foreach($casos_ejemplo as $caso)
            <div class="ejemplo-card">
                <strong style="font-size: 0.95rem;">Ejemplo #{{ $loop->iteration }}</strong>
                <table style="width: 100%; margin-top: 8px; border-collapse: collapse;">
                    <tr>
                        @if(!is_null($caso->entradas) && trim($caso->entradas) !== '')
                            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                                <strong style="font-size: 0.85rem; text-transform: uppercase;">Entrada:</strong>
                                <pre style="background: #ffffff; padding: 8px; border: 1px solid #ccc; font-family: monospace; font-size: 0.85rem; margin-top: 4px; white-space: pre-wrap; word-wrap: break-word;">{{ $caso->entradas }}</pre>
                            </td>
                            <td style="width: 50%; vertical-align: top;">
                                <strong style="font-size: 0.85rem; text-transform: uppercase;">Salida Esperada:</strong>
                                <pre style="background: #ffffff; padding: 8px; border: 1px solid #ccc; font-family: monospace; font-size: 0.85rem; margin-top: 4px; white-space: pre-wrap; word-wrap: break-word;">{{ $caso->salidas }}</pre>
                            </td>
                        @else
                            <td style="width: 100%; vertical-align: top;">
                                <strong style="font-size: 0.85rem; text-transform: uppercase;">Salida Esperada:</strong>
                                <pre style="background: #ffffff; padding: 8px; border: 1px solid #ccc; font-family: monospace; font-size: 0.85rem; margin-top: 4px; white-space: pre-wrap; word-wrap: break-word;">{{ $caso->salidas }}</pre>
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        @endforeach
    @endif
</body>

</html>
