@php
    $idCursoRes = $id_curso ?? ($res_certamen && $res_certamen->certamen ? $res_certamen->certamen->id_curso : null);
    $breadcrumbsResolver = [["nombre" => "Cursos", "route" => route("cursos.listado")]];
    if (!empty($idCursoRes)) {
        $breadcrumbsResolver[] = ["nombre" => "Problemas", "route" => route('problemas.listado', ['id' => $idCursoRes])];
        $breadcrumbsResolver[] = ["nombre" => $problema->nombre, "route" => route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $idCursoRes])];
    } else {
        $breadcrumbsResolver[] = ["nombre" => $problema->nombre];
    }
    $breadcrumbsResolver[] = ["nombre" => "Resolver"];
@endphp
@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre . ' - Resolver', 'breadcrumbs' => $breadcrumbsResolver])
@section('content')
    <div class="container-fluid px-4 pb-4">
        @include('components.alert')
        <div class="row">
            <div class="col-sm col-xs-12">
                
                @include('plataforma.problemas.componentes.card_editor')
            </div>
            <div class="col-sm-4 col-xs-12">
                @if(isset($res_certamen))
                @include('plataforma.problemas.componentes.card_timer_certamen')
                @endif
                @include('plataforma.problemas.componentes.card_enviar_solucion')
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/ace-builds/src-min/ace.js') }}" type="text/javascript" charset="utf-8"></script>
    <script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script>
    <script>
        @if(isset($res_certamen))
        // Protección contra retorno del navegador tras finalizar evaluación
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2) || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
        @endif

        var editor = ace.edit("editor");
        var formSubmited = false
        const lenguajes = {
            48: 'c_cpp',
            52: 'c_cpp',
            49: 'c_cpp',
            54: 'c_cpp',
            50: 'c_cpp',
            53: 'c_cpp',
            51: 'csharp',
            62: 'java',
            70: 'python',
            71: 'python',
            92: 'python',
            82: 'sql',
        }
        var set_lenguaje = lenguajes[{{ strtolower($last_envio->ProblemaLenguaje->lenguaje->codigo) }}] || 'c_cpp';
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/" + set_lenguaje);
        editor.setOptions({
            fontSize: "10pt"
        })


        function change_language(item) {
            if (item && item.value && lenguajes[item.value]) {
                editor.session.setMode("ace/mode/" + lenguajes[item.value]);
            }
            if (item && item.selectedIndex >= 0 && item.options[item.selectedIndex]) {
                document.querySelector('#lenguaje_save').value = item.options[item.selectedIndex].id || item.value;
            }
        }
        document.querySelector('#evaluacion_form').addEventListener('submit', e => {
            e.preventDefault();
            formSubmited = true
            try { localStorage.removeItem(draftStorageKey); } catch (_) {}
            submitCodigo()
        });
        document.querySelector('#guardarForm').addEventListener('submit', e => {
            const btnGuardar = document.getElementById('boton_guardar');
            if (btnGuardar && btnGuardar.disabled) {
                e.preventDefault();
                return false;
            }
            document.querySelector('#codigo_save').value = editor.getValue();
            let selectLenguaje = document.querySelector('#lenguaje');
            if (selectLenguaje && selectLenguaje.selectedIndex >= 0) {
                let opt = selectLenguaje.options[selectLenguaje.selectedIndex];
                document.querySelector('#lenguaje_save').value = opt.id || opt.value;
            }
            formSubmited = true;
            try { localStorage.removeItem(draftStorageKey); } catch (_) {}
            if (btnGuardar) {
                btnGuardar.disabled = true;
            }
        });

        // Autosave local (localStorage) + sincronización periódica en segundo plano
        const draftStorageKey = 'tutorbot_draft_{{ auth()->id() }}_{{ $problema->codigo }}_{{ isset($res_certamen) ? $res_certamen->token : ($id_curso ?? 0) }}';
        let editorDirty = false;
        let draftDebounceTimer = null;

        try {
            const savedDraft = localStorage.getItem(draftStorageKey);
            if (savedDraft && savedDraft.trim().length > 0 && editor.getValue().trim().length === 0) {
                editor.setValue(savedDraft, -1);
            }
        } catch (_) {}

        editor.session.on('change', function () {
            editorDirty = true;
            if (draftDebounceTimer) clearTimeout(draftDebounceTimer);
            draftDebounceTimer = setTimeout(function () {
                try {
                    localStorage.setItem(draftStorageKey, editor.getValue());
                } catch (_) {}
            }, 800);
        });

        setInterval(function () {
            if (!editorDirty || formSubmited) return;
            const guardarFormEl = document.querySelector('#guardarForm');
            if (!guardarFormEl) return;
            editorDirty = false;
            const formData = new FormData(guardarFormEl);
            formData.set('codigo_save', editor.getValue());
            const selectLenguaje = document.querySelector('#lenguaje');
            if (selectLenguaje && selectLenguaje.selectedIndex >= 0) {
                const opt = selectLenguaje.options[selectLenguaje.selectedIndex];
                formData.set('lenguaje_save', opt.id || opt.value);
            }
            fetch(guardarFormEl.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const statusEl = document.getElementById('autosave-status');
                if (statusEl) {
                    const now = new Date();
                    const hhmm = now.toTimeString().slice(0, 5);
                    statusEl.innerHTML = '<i class="fa fa-check-circle text-success me-1"></i>Guardado ' + hhmm;
                    statusEl.style.display = 'inline-block';
                }
            }).catch(function () {
                editorDirty = true;
            });
        }, 60000);

        window.addEventListener("beforeunload", function (e) {
            if (editorDirty && !formSubmited) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
@endpush
