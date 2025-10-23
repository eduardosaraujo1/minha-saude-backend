<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Actions\Auth\DTO\RegisterFormData;
use App\Domain\Actions\Auth\EmailLogin;
use App\Domain\Actions\Auth\GoogleLogin;
use App\Domain\Actions\Auth\Logout;
use App\Domain\Actions\Auth\Register;
use App\Domain\Actions\Auth\RequestVerificationEmail;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegisterRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

class AuthController extends Controller
{
    /**
     * Enviar código de login por e-mail
     *
     * Etapa intermediária do login via e-mail.
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
            }

            if ($message === ExceptionDictionary::INCORRECT_AUTH_CODE) {
                abort(401, 'Código inválido');
            }

            abort(500, 'Erro interno no servidor');
        }

        $loginResult = $result->getOrThrow();

        return response()->json($loginResult->toArray());
    }

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

    public function logout(Logout $logoutAction)
    {
        $attempt = $logoutAction->execute();

        if ($attempt->isFailure()) {
            Log::warning('Logout failed: may be no authenticated user, which should be caught by the middleware');
            abort(400, 'Usuário não autenticado');
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

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

        return $register->toArray();
    }
}
