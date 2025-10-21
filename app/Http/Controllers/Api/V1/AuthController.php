<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Actions\Auth\DTO\RegisterFormData;
use App\Domain\Actions\Auth\EmailLogin;
use App\Domain\Actions\Auth\GoogleLogin;
use App\Domain\Actions\Auth\Register;
use App\Domain\Actions\Auth\RequestVerificationEmail;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegisterRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Enviar código de login por e-mail
     */
    public function sendEmail(Request $request, RequestVerificationEmail $requestVerificationEmail)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $validated['email'];

        // Armazenar no cache por 30 minutos (1800 segundos)
        $result = $requestVerificationEmail->execute($email);

        if ($result->isFailure()) {
            abort(500, 'Unexpected error occoured');
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Login via código de e-mail
     */
    public function loginEmail(Request $request, EmailLogin $emailLogin)
    {
        $request->validate([
            'email' => 'required|email',
            'codigoEmail' => 'required|digits:6',
        ]);

        $email = $request->email;
        $code = $request->codigoEmail;

        // Attempt auth
        $result = $emailLogin->execute($email, $code);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();
            $message = $error?->getMessage();

            if ($message === ExceptionDictionary::EMAIL_NOT_FOUND) {
                abort(401, 'Pedido de e-mail não encontrado');

                return;
            }

            if ($message === ExceptionDictionary::INCORRECT_AUTH_CODE) {
                abort(401, 'Código inválido');

                return;

            }

            abort(500, 'Erro interno no servidor');
        }

        $loginResult = $result->getOrThrow();

        return response()->json([
            'isRegistered' => $loginResult->isRegistered,
            'sessionToken' => $loginResult->sessionToken,
            'registerToken' => $loginResult->registerToken,
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
