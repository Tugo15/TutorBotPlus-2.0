<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/sessions/revoke/{id?}', function (Request $request, $id = null) {
    $targetUserId = $id ?? $request->user()->id;
    $targetUser = \App\Models\User::find($targetUserId);

    if (!$targetUser) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    $service = app(\App\Services\SingleSessionService::class);
    $service->forceInvalidateSession($targetUser);

    return response()->json([
        'success' => true,
        'message' => 'Sesiones y tokens invalidados forzadamente para el usuario: ' . $targetUser->username
    ]);
});
