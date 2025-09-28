<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_google_login_authenticates_user(): void
    {
        // Criar usuário para simular a autenticação

        // Criar mock do GoogleAuthService

        // Simular a requisição de login via Google

        // Verificar se usuário está autenticado
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
        
    }
}
