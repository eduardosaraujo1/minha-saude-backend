<?php

use App\Data\Models\Admin;
use App\Data\Models\Document;
use App\Data\Models\Export;
use App\Data\Models\Share;
use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user model can be created with all fields', function () {
    $userData = [
        'name' => 'Test User',
        'cpf' => '123.456.789-00',
        'metodo_autenticacao' => UserAuthMethod::Email,
        'email' => 'test@example.com',
        'data_nascimento' => new DateTime('1990-01-01'),
        'telefone' => '+55 11 99999-9999',
    ];

    $user = User::create($userData);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toEqual($userData['name']);
    expect($user->cpf)->toEqual($userData['cpf']);
    expect($user->email)->toEqual($userData['email']);
    expect($user->metodo_autenticacao)->toEqual(UserAuthMethod::Email);
    expect($user->id)->not->toBeNull();
});

test('user has documents relationship', function () {
    $user = User::factory()->create();
    $documents = Document::factory()->count(3)->forUser($user)->create();

    expect($user->documents)->toHaveCount(3);
    expect($user->documents->first())->toBeInstanceOf(Document::class);
});

test('user has shares relationship', function () {
    $user = User::factory()->create();
    $shares = Share::factory()->count(2)->forUser($user)->create();

    expect($user->shares)->toHaveCount(2);
    expect($user->shares->first())->toBeInstanceOf(Share::class);
});

test('document model can be created with all fields', function () {
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

    expect($document)->toBeInstanceOf(Document::class);
    expect($document->titulo)->toEqual($documentData['titulo']);
    expect($document->is_processing)->toBeTrue();
    expect($document->user_id)->toEqual($user->id);

    // Test that date casting works properly
    expect($document->data_documento)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('document belongs to user', function () {
    $user = User::factory()->create();
    $document = Document::factory()->forUser($user)->create();

    expect($document->user)->toBeInstanceOf(User::class);
    expect($document->user->id)->toEqual($user->id);
});

test('document can have shares', function () {
    $user = User::factory()->create();
    $document = Document::factory()->forUser($user)->create();
    $share = Share::factory()->forUser($user)->create();

    $document->shares()->attach($share);

    expect($document->shares)->toHaveCount(1);
    expect($document->shares->first()->id)->toEqual($share->id);
});

test('share model can be created with all fields', function () {
    $user = User::factory()->create();
    $shareData = [
        'codigo' => 'ABC12345',
        'data_primeiro_uso' => now()->subHours(2), // Use Carbon datetime
        'expirado' => false, // Boolean instead of string
        'user_id' => $user->id,
    ];

    $share = Share::create($shareData);

    expect($share)->toBeInstanceOf(Share::class);
    expect($share->codigo)->toEqual($shareData['codigo']);
    expect($share->expirado)->toBeFalse();
    expect($share->user_id)->toEqual($user->id);

    // Test that datetime casting works properly
    expect($share->data_primeiro_uso)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('share belongs to user', function () {
    $user = User::factory()->create();
    $share = Share::factory()->forUser($user)->create();

    expect($share->user)->toBeInstanceOf(User::class);
    expect($share->user->id)->toEqual($user->id);
});

test('share can have documents', function () {
    $user = User::factory()->create();
    $share = Share::factory()->forUser($user)->create();
    $document = Document::factory()->forUser($user)->create();

    $share->documents()->attach($document);

    expect($share->documents)->toHaveCount(1);
    expect($share->documents->first()->id)->toEqual($document->id);
});

test('admin model can be created', function () {
    $adminData = [
        'username' => 'Admin User',
        'password' => 'password123',
    ];

    $admin = Admin::create($adminData);

    expect($admin)->toBeInstanceOf(Admin::class);
    expect($admin->username)->toEqual($adminData['username']);
});

test('export model can be created', function () {
    $exportData = [
        'file_path' => fake()->filePath(),
        'user_id' => User::factory()->create()->id,
    ];
    $export = Export::factory()->create($exportData);

    expect($export)->toBeInstanceOf(Export::class);
});

test('models use correct casts', function () {
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
    expect($user->data_nascimento)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($user->metodo_autenticacao)->toBeInstanceOf(UserAuthMethod::class);
    expect($user->metodo_autenticacao)->toEqual(UserAuthMethod::Google);

    // Test Document casts
    expect($document->is_processing)->toBeBool();
    expect($document->is_processing)->toBeTrue();
    expect($document->data_documento)->toBeInstanceOf(\Carbon\Carbon::class);

    // Test Share casts
    expect($share->expirado)->toBeBool();
    expect($share->expirado)->toBeFalse();
    expect($share->data_primeiro_uso)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('cast conversion from database strings', function () {
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
    expect($user->data_nascimento)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($user->data_nascimento->format('Y-m-d'))->toEqual('1995-06-15');
    expect($user->metodo_autenticacao)->toBeInstanceOf(UserAuthMethod::class);
    expect($user->metodo_autenticacao)->toEqual(UserAuthMethod::Email);
});

test('boolean cast from database', function () {
    $document = Document::factory()->create();
    $share = Share::factory()->create();

    // Update database with string/numeric boolean representations
    DB::table('documents')->where('id', $document->id)->update(['is_processing' => '1']);
    DB::table('shares')->where('id', $share->id)->update(['expirado' => '0']);

    // Refresh models
    $document->refresh();
    $share->refresh();

    // Test proper boolean casting
    expect($document->is_processing)->toBeBool();
    expect($document->is_processing)->toBeTrue();
    expect($share->expirado)->toBeBool();
    expect($share->expirado)->toBeFalse();
});