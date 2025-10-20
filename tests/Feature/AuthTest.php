<?php

namespace Tests\Feature;

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\DTO\GoogleUserInfo;
use App\Data\Services\Google\GoogleService;
use App\Utils\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_authenticates_user(): void
    {
        // Criar usuário para simular a autenticação
        $fakeGoogleId = '38267382323';
        $fakeServerAuth = '4/0jdsSksfr93Ljsdnu2...';
        $user = User::factory()->create([
            'metodo_autenticacao' => UserAuthMethod::Google,
            'google_id' => $fakeGoogleId,
        ]);

        // Criar mock do GoogleService
        $this->mock(
            GoogleService::class,
            function (\Mockery\MockInterface $mock) use ($user, $fakeGoogleId, $fakeServerAuth) {
                $mock->shouldReceive('getUserInfo')
                    ->once()
                    ->with($fakeServerAuth)
                    ->andReturn(Result::success(
                        new GoogleUserInfo(
                            $fakeGoogleId,
                            $user->email,
                        )
                    ));

            });

        // Simular a requisição de login via Google
        $response = $this->post(route('auth.login.google'), [
            'tokenOauth' => $fakeServerAuth,
        ]);

        // Verificar se usuário está autenticado
        $response->assertStatus(200)
            ->assertJson([
                'isRegistered' => true,
                'registerToken' => null,
            ]);
    }

    public function test_email_code_request()
    {
        // Criar mock do EmailService

        // Simular a requisição de envio do código via E-mail

        // Verificar se o código foi enviado
    }

    public function test_email_login_authenticates_user(): void
    {
        // Criar usuário para simular a autenticação

        // Criar mock do EmailCodeVerificationService

        // Simular a requisição de login via E-mail

        // Verificar se usuário está autenticado
    }

    public function test_register_user_with_valid_token(): void
    {
        // Dados para simular registro

        // Adicionar registerToken temporário no banco

        // Simular a requisição de login via E-mail

        // Verificar se usuário está autenticado
    }

    public function test_logout_invalidates_sanctum_token()
    {
        // Criar usuário autenticado

        // Simular a requisição de logout

        // Verificar se o token foi invalidado
    }
}
