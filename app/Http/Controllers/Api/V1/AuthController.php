<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Models\User;
use App\Domain\Actions\GoogleLogin;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Enviar código de login por e-mail
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $code = rand(100000, 999999);

        // Armazenar no cache por 30 minutos (1800 segundos)
        Cache::put("login_email_code_{$email}", $code, 1800);

        Mail::to($email)->send(new \App\Mail\LoginEmailCode($code));

        return response()->json([
            'status' => 'success',
            'message' => 'Código enviado ao e-mail.',
        ]);
    }

    /**
     * Login via código de e-mail
     */
    public function loginEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo_email' => 'required|digits:6',
        ]);

        $email = $request->email;
        $code = $request->codigo_email;

        $cacheKey = "login_email_code_{$email}";
        $cachedCode = Cache::get($cacheKey);

        if (! $cachedCode || $cachedCode != $code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Código inválido',
            ], 401);
        }

        Cache::forget($cacheKey);

        // Criar usuário se não existir
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Usuário',
                'password' => bcrypt(Str::random(12)),
            ]
        );

        $sessionToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'is_registered' => true,
            'session_token' => $sessionToken,
            'user' => $user,
        ]);
    }

    /**
     * Login via Google usando Server Authorization Token
     */
    public function loginGoogle(Request $request, GoogleLogin $googleLogin)
    {
        $request->validate([
            'tokenOauth' => 'required|string',
        ]);
        $token = $request->tokenOauth;

        $loginResult = $googleLogin->execute($token);

        if ($loginResult->isFailure()) {
            $error = $loginResult->tryGetFailure();
            $message = $error?->getMessage();

            if ($message === ExceptionDictionary::INVALID_OAUTH_TOKEN) {
                abort(401, ExceptionDictionary::INVALID_OAUTH_TOKEN);
            } else {
                abort(500, 'Erro interno no servidor');
            }
        }

        return $loginResult->getOrThrow()->toArray();
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    /**
     * Registrar novo usuário
     */
    public function register(Request $request)
    {
        $request->validate([
            'user.nome_completo' => 'required|string',
            'user.cpf' => 'required|string|unique:users,cpf',
            'user.data_nascimento' => 'required|date',
            'user.telefone' => 'required|string',
            'user.email' => 'nullable|email|unique:users,email',
            'register_token' => 'required|string',
        ]);

        $userData = $request->user;

        $user = User::create([
            'name' => $userData['nome_completo'],
            'cpf' => $userData['cpf'],
            'data_nascimento' => $userData['data_nascimento'],
            'telefone' => $userData['telefone'],
            'email' => $userData['email'] ?? null,
            'password' => bcrypt(Str::random(12)),
        ]);

        $sessionToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'session_token' => $sessionToken,
            'user' => $user,
        ]);
    }
}
