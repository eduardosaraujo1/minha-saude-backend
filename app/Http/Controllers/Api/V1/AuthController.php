<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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
    public function loginGoogle(Request $request)
    {
        $request->validate([
            'token_oauth' => 'required|string',
        ]);

        $token = $request->token_oauth;

        // Obter dados do Google
        $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();

        // Verificar se usuário já existe por google_id ou email
        $user = User::firstOrCreate(
            ['google_id' => $googleId],
            [
                'email' => $email,
                'name' => $name,
                'password' => bcrypt(Str::random(12)),
            ]
        );

        // Caso o usuário exista apenas por email, vincular google_id
        if (! $user->google_id) {
            $user->google_id = $googleId;
            $user->save();
        }

        $sessionToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'is_registered' => true,
            'session_token' => $sessionToken,
            'user' => $user,
        ]);
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
     * Registrar novo usuário via payload {user:{cpf,nome_completo,data_nascimento,telefone,email}, register_token}
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
