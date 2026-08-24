<div class="px-4 pt-4">
    @if ($message = session()->has('succes'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <p class="text-black mb-0">{{ session()->get('succes') }}</p>
        </div>
    @endif
    @if ($message = session()->has('error'))
        <div class="alert alert-danger" role="alert">
            <p class="text-white mb-0">{{ session()->get('error') }}</p>
        </div>
    @endif
    @error('session')
        <div class="alert alert-warning text-white font-weight-bold" role="alert">
            <p class="text-white mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}</p>
        </div>
    @enderror
    @if ($message = session()->has('status'))
        <div class="alert alert-danger" role="alert">
            <p class="text-white mb-0">{{ session()->get('status') }}</p>
        </div>
    @endif
</div>
