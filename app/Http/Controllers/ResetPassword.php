<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Facades\Password;
class ResetPassword extends Controller
{
    public function show()
    {
        return response()
            ->view('auth.forgot-password')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            \Log::error('Error enviando correo de recuperación de contraseña: ' . $e->getMessage());
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No fue posible enviar el correo de recuperación en este momento. Inténtelo más tarde o contacte al administrador.']);
        }
     
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
