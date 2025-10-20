<?php

namespace Tests\Feature;

use App\Data\Models\Admin;
use App\Data\Models\Document;
use App\Data\Models\Export;
use App\Data\Models\Share;
use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModelSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_can_be_created_with_all_fields(): void
    {
        $userData = [
            'name' => 'Test User',
            'cpf' => '123.456.789-00',
            'metodo_autenticacao' => UserAuthMethod::Email,
            'email' => 'test@example.com',
            'data_nascimento' => new DateTime('1990-01-01'),
            'telefone' => '+55 11 99999-9999',
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['cpf'], $user->cpf);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals(UserAuthMethod::Email, $user->metodo_autenticacao);
        $this->assertNotNull($user->id);
    }

    public function test_user_has_documents_relationship(): void
    {
        $user = User::factory()->create();
        $documents = Document::factory()->count(3)->forUser($user)->create();

        $this->assertCount(3, $user->documents);
        $this->assertInstanceOf(Document::class, $user->documents->first());
    }

    public function test_user_has_shares_relationship(): void
    {
        $user = User::factory()->create();
        $shares = Share::factory()->count(2)->forUser($user)->create();

        $this->assertCount(2, $user->shares);
        $this->assertInstanceOf(Share::class, $user->shares->first());
    }

    public function test_document_model_can_be_created_with_all_fields(): void
    {
        $user = User::factory()->create();
        $documentData = [
            'titulo' => 'Test Document',
            'nome_paciente' => 'Patient Name',
            'nome_medico' => 'Dr. Medical',
            'tipo_documento' => 'Receita',
            'data_documento' => now()->subDays(30)->toDate(), // Use Carbon date
            'is_processing' => true, // Boolean instead of string
            'caminho_arquivo' => '/path/to/file.pdf',
            'user_id' => $user->id,
        ];

        $document = Document::create($documentData);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($documentData['titulo'], $document->titulo);
        $this->assertTrue($document->is_processing);
        $this->assertEquals($user->id, $document->user_id);
        // Test that date casting works properly
        $this->assertInstanceOf(\Carbon\Carbon::class, $document->data_documento);
    }

    public function test_document_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $document->user);
        $this->assertEquals($user->id, $document->user->id);
    }

    public function test_document_can_have_shares(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->forUser($user)->create();
        $share = Share::factory()->forUser($user)->create();

        $document->shares()->attach($share);

        $this->assertCount(1, $document->shares);
        $this->assertEquals($share->id, $document->shares->first()->id);
    }

    public function test_share_model_can_be_created_with_all_fields(): void
    {
        $user = User::factory()->create();
        $shareData = [
            'codigo' => 'ABC12345',
            'data_primeiro_uso' => now()->subHours(2), // Use Carbon datetime
            'expirado' => false, // Boolean instead of string
            'user_id' => $user->id,
        ];

        $share = Share::create($shareData);

        $this->assertInstanceOf(Share::class, $share);
        $this->assertEquals($shareData['codigo'], $share->codigo);
        $this->assertFalse($share->expirado);
        $this->assertEquals($user->id, $share->user_id);
        // Test that datetime casting works properly
        $this->assertInstanceOf(\Carbon\Carbon::class, $share->data_primeiro_uso);
    }

    public function test_share_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $share = Share::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $share->user);
        $this->assertEquals($user->id, $share->user->id);
    }

    public function test_share_can_have_documents(): void
    {
        $user = User::factory()->create();
        $share = Share::factory()->forUser($user)->create();
        $document = Document::factory()->forUser($user)->create();

        $share->documents()->attach($document);

        $this->assertCount(1, $share->documents);
        $this->assertEquals($document->id, $share->documents->first()->id);
    }

    public function test_admin_model_can_be_created(): void
    {
        $adminData = [
            'username' => 'Admin User',
            'password' => 'password123',
        ];

        $admin = Admin::create($adminData);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals($adminData['username'], $admin->username);
    }

    public function test_export_model_can_be_created()
    {
        $exportData = [
            'file_path' => fake()->filePath(),
            'user_id' => User::factory()->create()->id,
        ];
        $export = Export::factory()->create($exportData);

        $this->assertInstanceOf(Export::class, $export);
    }

    public function test_models_use_correct_casts(): void
    {
        // Test User casts with proper data types
        $birthDate = now()->subYears(25)->toDate();
        $user = User::factory()->create([
            'data_nascimento' => $birthDate,
            'metodo_autenticacao' => UserAuthMethod::Google, // Use enum instead of string
        ]);

        // Test Document casts with proper data types
        $documentDate = now()->subDays(15)->toDate();
        $document = Document::factory()->create([
            'is_processing' => true, // Boolean instead of string
            'data_documento' => $documentDate,
        ]);

        // Test Share casts with proper data types
        $firstUseDate = now()->subDays(3);
        $share = Share::factory()->create([
            'expirado' => false, // Boolean instead of string
            'data_primeiro_uso' => $firstUseDate,
        ]);

        // Test User casts
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->data_nascimento);
        $this->assertInstanceOf(UserAuthMethod::class, $user->metodo_autenticacao);
        $this->assertEquals(UserAuthMethod::Google, $user->metodo_autenticacao);

        // Test Document casts
        $this->assertIsBool($document->is_processing);
        $this->assertTrue($document->is_processing);
        $this->assertInstanceOf(\Carbon\Carbon::class, $document->data_documento);

        // Test Share casts
        $this->assertIsBool($share->expirado);
        $this->assertFalse($share->expirado);
        $this->assertInstanceOf(\Carbon\Carbon::class, $share->data_primeiro_uso);
    }

    public function test_cast_conversion_from_database_strings(): void
    {
        // Test that string values from database are properly cast
        $user = User::factory()->create();

        // Simulate database string values by directly updating the database
        DB::table('users')->where('id', $user->id)->update([
            'data_nascimento' => '1995-06-15',
            'metodo_autenticacao' => 'email',
        ]);

        // Refresh the model to get values from database
        $user->refresh();

        // Test that strings are properly cast to their respective types
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->data_nascimento);
        $this->assertEquals('1995-06-15', $user->data_nascimento->format('Y-m-d'));
        $this->assertInstanceOf(UserAuthMethod::class, $user->metodo_autenticacao);
        $this->assertEquals(UserAuthMethod::Email, $user->metodo_autenticacao);
    }

    public function test_boolean_cast_from_database(): void
    {
        $document = Document::factory()->create();
        $share = Share::factory()->create();

        // Update database with string/numeric boolean representations
        DB::table('documents')->where('id', $document->id)->update(['is_processing' => '1']);
        DB::table('shares')->where('id', $share->id)->update(['expirado' => '0']);

        // Refresh models
        $document->refresh();
        $share->refresh();

        // Test proper boolean casting
        $this->assertIsBool($document->is_processing);
        $this->assertTrue($document->is_processing);
        $this->assertIsBool($share->expirado);
        $this->assertFalse($share->expirado);
    }
}
