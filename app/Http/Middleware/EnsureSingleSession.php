<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SingleSessionService;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    protected SingleSessionService $sessionService;

    public function __construct(SingleSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();

            if (!$this->sessionService->isValidSession($user, $currentSessionId)) {
                $isForcedRemote = is_null($user->current_session_id);
                $mensaje = $isForcedRemote 
                    ? 'Tu sesión ha sido finalizada de manera remota por un administrador o ha expirado su tiempo de validez.' 
                    : 'Tu sesión ha sido cerrada debido a un inicio de sesión concurrente en otro dispositivo.';

                // Logout the invalidated session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $mensaje
                    ], 401);
                }

                return redirect()->route('login')->withErrors([
                    'session' => $mensaje
                ]);
            }
        }

        return $next($request);
    }
}
