<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Models\User;
use App\Domain\Actions\DTO\RegisterFormData;
use App\Domain\Actions\GoogleLogin;
use App\Domain\Actions\Register;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegisterRequest;
use Carbon\Carbon;
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
            'isRegistered' => true,
            'sessionToken' => $sessionToken,
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
    public function register(RegisterRequest $request, Register $registerAction)
    {
        $data = $request->validated();
        $userData = $data['user'];

        $registerResult = $registerAction->execute(new RegisterFormData(
            nome: $userData['nome'],
            cpf: $userData['cpf'],
            dataNascimento: Carbon::parse($userData['dataNascimento']),
            telefone: $userData['telefone'],
            registerToken: $data['registerToken'],
        ));

        if ($registerResult->isFailure()) {
            $error = $registerResult->tryGetFailure()?->getMessage();
            if ($error === ExceptionDictionary::INVALID_REGISTER_TOKEN) {
                abort(401, ExceptionDictionary::INVALID_REGISTER_TOKEN);
            }
            abort(500, $error ?? 'Erro interno no servidor');
        }

        $register = $registerResult->getOrThrow();
        $token = $register->sessionToken;
        $user = $register->user;

        return [
            'sessionToken' => $token,
            'user' => [
                'nome' => $user->name,
                'cpf' => $user->cpf,
                'dataNascimento' => $user->data_nascimento->format('Y-m-d'),
                'telefone' => $user->telefone,
            ],
        ];
    }
}
