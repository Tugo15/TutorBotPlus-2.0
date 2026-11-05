@php
    $toastSuccess = session('success') ?? session('succes');
    $toastError = session('error') ?? session('status') ?? ($errors->has('session') ? $errors->first('session') : null);
    $toastWarning = session('warning');
@endphp

@if($toastSuccess || $toastError || $toastWarning)
<style>
    /* Ocultar las alertas inline tradicionales cuando el toast flotante está activo para evitar duplicados y saltos de página */
    .card-body > .alert.alert-success,
    .card-body > .alert.alert-danger,
    #alert .alert {
        display: none !important;
    }

    .tbp-toast-container {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 10999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: 400px;
        width: calc(100% - 2.5rem);
        pointer-events: none;
    }

    .tbp-toast {
        pointer-events: auto;
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.14), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.06);
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: flex-start;
        padding: 0.9rem 1rem;
        gap: 0.85rem;
        transform: translateX(120%);
        opacity: 0;
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
    }

    .tbp-toast.show {
        transform: translateX(0);
        opacity: 1;
    }

    .tbp-toast.hiding {
        transform: translateX(120%);
        opacity: 0;
    }

    .tbp-toast-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
    }

    .tbp-toast-success .tbp-toast-icon {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }

    .tbp-toast-error .tbp-toast-icon {
        background: rgba(245, 54, 92, 0.15);
        color: #f5365c;
    }

    .tbp-toast-warning .tbp-toast-icon {
        background: rgba(251, 99, 64, 0.15);
        color: #fb6340;
    }

    .tbp-toast-body {
        flex: 1;
        min-width: 0;
    }

    .tbp-toast-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #32325d;
        margin-bottom: 0.15rem;
        line-height: 1.25;
    }

    .tbp-toast-msg {
        font-size: 0.78rem;
        color: #525f7f;
        margin-bottom: 0;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .tbp-toast-close {
        background: transparent;
        border: none;
        color: #8898aa;
        font-size: 1rem;
        line-height: 1;
        padding: 0.15rem;
        cursor: pointer;
        transition: color 0.15s ease;
        flex-shrink: 0;
    }

    .tbp-toast-close:hover {
        color: #32325d;
    }

    .tbp-toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        transform-origin: left;
        animation: tbpToastProgress 4.5s linear forwards;
    }

    .tbp-toast:hover .tbp-toast-progress {
        animation-play-state: paused;
    }

    .tbp-toast-success .tbp-toast-progress {
        background: linear-gradient(90deg, #2dce89, #2dcecc);
    }

    .tbp-toast-error .tbp-toast-progress {
        background: linear-gradient(90deg, #f5365c, #f56036);
    }

    .tbp-toast-warning .tbp-toast-progress {
        background: linear-gradient(90deg, #fb6340, #fbb140);
    }

    @keyframes tbpToastProgress {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }
</style>

<div class="tbp-toast-container" id="tbpToastContainer">
    @if($toastSuccess)
        <div class="tbp-toast tbp-toast-success" role="status" aria-live="polite">
            <div class="tbp-toast-icon">
                <i class="fa fa-check"></i>
            </div>
            <div class="tbp-toast-body">
                <div class="tbp-toast-title">Operación exitosa</div>
                <p class="tbp-toast-msg">{{ $toastSuccess }}</p>
            </div>
            <button type="button" class="tbp-toast-close" onclick="dismissTbpToast(this.closest('.tbp-toast'))" aria-label="Cerrar">&times;</button>
            <div class="tbp-toast-progress"></div>
        </div>
    @endif

    @if($toastError)
        <div class="tbp-toast tbp-toast-error" role="alert" aria-live="assertive">
            <div class="tbp-toast-icon">
                <i class="fa fa-exclamation-circle"></i>
            </div>
            <div class="tbp-toast-body">
                <div class="tbp-toast-title">Ocurrió un problema</div>
                <p class="tbp-toast-msg">{{ $toastError }}</p>
            </div>
            <button type="button" class="tbp-toast-close" onclick="dismissTbpToast(this.closest('.tbp-toast'))" aria-label="Cerrar">&times;</button>
            <div class="tbp-toast-progress"></div>
        </div>
    @endif

    @if($toastWarning)
        <div class="tbp-toast tbp-toast-warning" role="status" aria-live="polite">
            <div class="tbp-toast-icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="tbp-toast-body">
                <div class="tbp-toast-title">Atención</div>
                <p class="tbp-toast-msg">{{ $toastWarning }}</p>
            </div>
            <button type="button" class="tbp-toast-close" onclick="dismissTbpToast(this.closest('.tbp-toast'))" aria-label="Cerrar">&times;</button>
            <div class="tbp-toast-progress"></div>
        </div>
    @endif
</div>

<script>
    function dismissTbpToast(toastEl) {
        if (!toastEl || toastEl.classList.contains('hiding')) return;
        toastEl.classList.remove('show');
        toastEl.classList.add('hiding');
        setTimeout(() => {
            if (toastEl && toastEl.parentNode) {
                toastEl.parentNode.removeChild(toastEl);
            }
        }, 350);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const toasts = document.querySelectorAll('#tbpToastContainer .tbp-toast');
        toasts.forEach((toast, idx) => {
            setTimeout(() => toast.classList.add('show'), 40 + idx * 80);

            let remaining = 4500;
            let start = Date.now();
            let timer = setTimeout(() => dismissTbpToast(toast), remaining);

            toast.addEventListener('mouseenter', () => {
                clearTimeout(timer);
                remaining -= (Date.now() - start);
            });

            toast.addEventListener('mouseleave', () => {
                start = Date.now();
                timer = setTimeout(() => dismissTbpToast(toast), Math.max(remaining, 1000));
            });
        });
    });
</script>
@endif

