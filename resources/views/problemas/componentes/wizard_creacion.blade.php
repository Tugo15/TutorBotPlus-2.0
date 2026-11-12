@php
    $pasoActual = $paso ?? 1;
    $totalCasos = isset($casos) ? count($casos) : ((isset($caso) && $caso) ? 1 : 0);
@endphp

<style>
    .wizard-stepper-card {
        position: relative;
        z-index: 2;
        background: #ffffff;
        border-radius: 0.75rem;
    }
    .wizard-step-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .wizard-step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }
    .wizard-step-circle.active {
        background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.18);
    }
    .wizard-step-circle.completed {
        background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        color: #ffffff;
    }
    .wizard-step-circle.pending {
        background: #f6f9fc;
        color: #8898aa;
        border: 1.5px solid #dee2e6;
    }
    .wizard-connector {
        width: 70px;
        height: 3px;
        border-radius: 3px;
        background: #e9ecef;
        margin: 0 0.75rem;
        overflow: hidden;
    }
    .wizard-connector.completed {
        background: linear-gradient(90deg, #2dce89 0%, #5e72e4 100%);
    }
</style>

<div class="card wizard-stepper-card shadow-xs border mb-3">
    <div class="card-body py-2 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        @if($pasoActual == 2 && isset($problema))
            <div class="d-none d-lg-block" style="min-width: 140px;"></div>
        @endif

        <!-- Stepper central -->
        <div class="d-flex align-items-center justify-content-center mx-auto py-1">
            <!-- Paso 1 -->
            <div class="wizard-step-item">
                <div class="wizard-step-circle {{ $pasoActual == 1 ? 'active' : 'completed' }}">
                    @if($pasoActual > 1)
                        <i class="fa fa-check" style="font-size: 0.75rem;"></i>
                    @else
                        1
                    @endif
                </div>
                <div class="lh-1">
                    <div class="text-xs font-weight-bold {{ $pasoActual == 1 ? 'text-dark' : 'text-success' }}">
                        Crear Problema
                    </div>
                    <small class="text-xxs text-secondary d-none d-sm-inline">Datos y enunciado</small>
                </div>
            </div>

            <!-- Línea conectora -->
            <div class="wizard-connector {{ $pasoActual > 1 ? 'completed' : '' }}"></div>

            <!-- Paso 2 -->
            <div class="wizard-step-item">
                <div class="wizard-step-circle {{ $pasoActual == 2 ? 'active' : 'pending' }}">
                    2
                </div>
                <div class="lh-1">
                    <div class="text-xs font-weight-bold {{ $pasoActual == 2 ? 'text-dark' : 'text-secondary' }}">
                        Casos de Prueba
                    </div>
                    <small class="text-xxs text-secondary d-none d-sm-inline">Entradas y salidas</small>
                </div>
            </div>
        </div>

        <!-- Acción derecha -->
        @if($pasoActual == 2 && isset($problema))
            <div class="d-flex align-items-center justify-content-end gap-2" style="min-width: 140px;">
                <span class="badge bg-light text-dark border text-xxs px-2 py-1">
                    {{ $totalCasos }} caso(s)
                </span>
                <a href="{{ route('casos_pruebas.assign', array_merge(['id' => $problema->id, 'finalizar_wizard' => 1], (isset($id_curso) && $id_curso ? ['id_curso' => $id_curso] : []))) }}"
                   class="btn btn-xs btn-success mb-0">
                    <i class="fa fa-check me-1"></i> Finalizar
                </a>
            </div>
        @endif
    </div>
</div>
