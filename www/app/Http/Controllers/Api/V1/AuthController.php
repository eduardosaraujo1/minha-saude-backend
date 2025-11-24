<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ReauthenticateRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Modules\User\DTOs\Auth\RegisterFormData;
use App\Modules\User\UserModule;
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
    public function sendEmail(Request $request, UserModule $userModule)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $validated['email'];

        // Armazenar no cache por 30 minutos (1800 segundos)
        $result = $userModule->requestVerificationEmail($email);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function loginEmail(Request $request, UserModule $userModule)
    {
        $request->validate([
            'email' => 'required|email',
            'codigoEmail' => 'required|digits:6',
        ]);

        $email = $request->email;
        $code = $request->codigoEmail;

        // Attempt auth
        $result = $userModule->emailLogin($email, $code);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error?->message);
        }

        $loginResult = $result->getOrThrow();

        return response()->json($loginResult->toArray());
    }

    public function loginGoogle(Request $request, UserModule $userModule)
    {
        $request->validate([
            'tokenOauth' => 'required|string',
        ]);
        $token = $request->tokenOauth;

        $loginResult = $userModule->googleLogin($token);

        if ($loginResult->isFailure()) {
            $error = $loginResult->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json($loginResult->getOrThrow()->toArray());
    }

    public function logout(UserModule $userModule)
    {
        $attempt = $userModule->logout();

        if ($attempt->isFailure()) {
            Log::warning('Logout failed: may be no authenticated user, which should have be caught by the middleware');
            $error = $attempt->tryGetFailure();
            abort($error->code, $error->message);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function register(RegisterRequest $request, UserModule $userModule)
    {
        $data = $request->validated();
        $userData = $data['user'];

        $registerResult = $userModule->register(new RegisterFormData(
            nome: $userData['nome'],
            cpf: $userData['cpf'],
            dataNascimento: Carbon::parse($userData['dataNascimento']),
            telefone: $userData['telefone'],
            registerToken: $data['registerToken'],
        ));

        if ($registerResult->isFailure()) {
            $error = $registerResult->tryGetFailure();

            abort($error->code, $error->message);
        }

        $register = $registerResult->getOrThrow();

        return response()->json($register->toArray());
    }

    public function reauthenticate(ReauthenticateRequest $request, UserModule $userModule)
    {
        $validated = $request->validated();

        $formData = \App\Modules\User\DTOs\Auth\ReauthenticateFormData::fromRequest($validated);

        $result = $userModule->reauthenticate($formData);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        $reauthResult = $result->getOrThrow();

        return response()->json($reauthResult->toArray());
    }
}
