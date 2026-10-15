<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Maneja el login y registro automático con Google y Facebook
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
            'token'    => 'required|string',
            'email'    => 'nullable|email',
            'name'     => 'nullable|string',
            'picture'  => 'nullable|string',
            'id'       => 'nullable|string',
        ]);

        $provider = $request->input('provider');
        $token = $request->input('token');

        $email = $request->input('email');
        $name = $request->input('name');
        $picture = $request->input('picture');
        $providerId = $request->input('id');

        // 1. Verificación e introspección del token según el proveedor
        if ($provider === 'google') {
            try {
                // Verificamos con el endpoint oficial de Google para tokens OAuth / id_token
                $response = Http::get("https://oauth2.googleapis.com/tokeninfo", [
                    'id_token' => $token,
                ]);

                if ($response->failed()) {
                    // Si falla id_token, probamos como access_token
                    $response = Http::withHeaders([
                        'Authorization' => "Bearer {$token}"
                    ])->get("https://www.googleapis.com/oauth2/v3/userinfo");
                }

                if ($response->successful()) {
                    $googleData = $response->json();
                    $email = $googleData['email'] ?? $email;
                    $name = $googleData['name'] ?? $name;
                    $picture = $googleData['picture'] ?? $picture;
                    $providerId = $googleData['sub'] ?? $providerId;
                }
            } catch (\Throwable $e) {
                Log::warning("Error verificando token de Google: " . $e->getMessage());
            }
        } elseif ($provider === 'facebook') {
            try {
                $response = Http::get("https://graph.facebook.com/me", [
                    'fields'       => 'id,name,email,picture.type(large)',
                    'access_token' => $token,
                ]);

                if ($response->successful()) {
                    $fbData = $response->json();
                    $email = $fbData['email'] ?? $email;
                    $name = $fbData['name'] ?? $name;
                    $picture = $fbData['picture']['data']['url'] ?? $picture;
                    $providerId = $fbData['id'] ?? $providerId;
                }
            } catch (\Throwable $e) {
                Log::warning("Error verificando token de Facebook: " . $e->getMessage());
            }
        }

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener un correo electrónico válido de la cuenta.'
            ], 422);
        }

        // 2. Buscar si el usuario ya existe por email
        $user = User::where('email', $email)->first();

        if (!$user) {
            // 3. Crear usuario automáticamente si no existe
            $user = new User();
            $user->name = $name ?: 'Usuario ' . ucfirst($provider);
            $user->email = $email;
            $user->password = Hash::make(Str::random(32));
            $user->nivel = 1; // 1 = Cliente regular
            $user->type = 1;  // 1 = Persona natural
            $user->status = '1'; // Activo
            $user->email_verified_at = now();

            if (!empty($picture)) {
                $user->image = $picture;
            }

            $user->save();
        } else {
            // Si el usuario existe pero no tiene foto, actualizarla
            if (!empty($picture) && empty($user->image)) {
                $user->image = $picture;
                $user->save();
            }
        }

        // 4. Crear token Sanctum para la app móvil
        $apiToken = $user->createToken('promarketapp')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $apiToken,
            'user'    => $user,
        ]);
    }

    /**
     * Cerrar sesión y revocar el token actual
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada con éxito.'
        ]);
    }
}
