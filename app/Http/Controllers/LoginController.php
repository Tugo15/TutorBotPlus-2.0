<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use App\Services\SingleSessionService;

class LoginController extends Controller
{
    protected SingleSessionService $sessionService;

    public function __construct(SingleSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Register current session as the only active session for this user
            $this->sessionService->registerSession(Auth::user(), $request->session()->getId());

            // Limpiar redirecciones residuales que apunten a la raíz del host o fuera de /tutorbot
            $intended = session('url.intended');
            if ($intended && (!str_contains($intended, '/tutorbot') || rtrim($intended, '/') === 'https://cernicalo.ucsc.cl' || rtrim($intended, '/') === 'http://cernicalo.ucsc.cl')) {
                session()->forget('url.intended');
            }

            return redirect()->intended(url('/cursos'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->sessionService->invalidateSession(Auth::user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
