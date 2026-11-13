<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ChangePassword extends Controller
{

    public function show(Request $request, $token)
    {
        $tokenInvalido = false;
        if (strlen((string) $token) < 20) {
            $tokenInvalido = true;
        } elseif ($request->filled('email')) {
            $user = User::where('email', $request->query('email'))->first();
            if (!$user || !Password::tokenExists($user, $token)) {
                $tokenInvalido = true;
            }
        } elseif (!\Illuminate\Support\Facades\DB::table('password_reset_tokens')->exists()) {
            $tokenInvalido = true;
        }

        return response()
            ->view('auth.reset_password', ['token' => $token, 'tokenInvalido' => $tokenInvalido])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        $msgError = in_array($status, [Password::INVALID_USER, Password::INVALID_TOKEN])
            ? 'El enlace de recuperación es inválido o ha expirado para el correo indicado.'
            : __($status);

        return back()->withInput($request->only('email'))->withErrors(['email' => [$msgError]]);
    }
}
