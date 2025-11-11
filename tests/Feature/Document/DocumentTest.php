<?php

use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use App\Modules\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\mock;

// ==================== Upload Tests ====================

test('can upload document with all fields', function () {
    // Arrange: Create user and mock file storage
    $user = User::factory()->create();

    $fileStorage = mock(FileStoragePort::class);
    $fileStorage->shouldReceive('store')
        ->once()
        ->andReturn(true);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Act: Upload document
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
        'titulo' => 'Medical Report',
        'nomePaciente' => 'João Silva',
        'nomeMedico' => 'Dr. Maria',
        'tipoDocumento' => 'Exame',
        'dataDocumento' => '2025-01-15',
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Document created in database
    $this->assertDatabaseHas('documents', [
        'user_id' => $user->id,
        'titulo' => 'Medical Report',
        'nome_paciente' => 'João Silva',
        'nome_medico' => 'Dr. Maria',
        'tipo_documento' => 'Exame',
        'data_documento' => '2025-01-15',
    ]);
});

test('can upload document with minimal fields', function () {
    // Arrange: Create user and mock file storage
    $user = User::factory()->create();

    $fileStorage = mock(FileStoragePort::class);
    $fileStorage->shouldReceive('store')
        ->once()
        ->andReturn(true);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Act: Upload with only required field
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
    ]);

    // Assert: Successful with default title
    $response->assertSuccessful();

    $this->assertDatabaseHas('documents', [
        'user_id' => $user->id,
        'titulo' => 'Documento sem título',
    ]);
});

test('cannot upload document without authentication', function () {
    // Arrange: Create fake file
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Act: Try to upload without authentication
    $response = $this->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
    ]);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot upload document without file', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    // Act: Try to upload without file
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'titulo' => 'Test',
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['arquivos']);
});

test('cannot upload non-pdf file', function () {
    // Arrange: Create user and non-PDF file
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

    // Act: Try to upload non-PDF
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['arquivos']);
});

test('cannot upload file exceeding size limit', function () {
    // Arrange: Create user and large file (>10MB)
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 11000, 'application/pdf');

    // Act: Try to upload large file
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['arquivos']);
});

test('returns error when file storage fails on upload', function () {
    // Arrange: Create user and mock file storage to fail
    $user = User::factory()->create();

    $fileStorage = mock(FileStoragePort::class);
    $fileStorage->shouldReceive('store')
        ->once()
        ->andReturn(false);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Act: Try to upload
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
    ]);

    // Assert: Error response
    $response->assertStatus(500)
        ->assertJson(['message' => 'unexpected_error']);

    // Assert: No document created
    $this->assertDatabaseMissing('documents', [
        'user_id' => $user->id,
    ]);
});

test('validates date format on upload', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Act: Try to upload with invalid date
    $response = $this->actingAs($user)->postJson('/api/v1/documents/upload', [
        'arquivos' => $file,
        'dataDocumento' => '15/01/2025', // Wrong format
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dataDocumento']);
});

// ==================== Index Tests ====================

test('can list all documents', function () {
    // Arrange: Create user and multiple documents
    $user = User::factory()->create();

    Document::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    // Act: Get documents list
    $response = $this->actingAs($user)->getJson('/api/v1/documents');

    // Assert: All documents returned
    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');

    // Assert: Response structure
    expect($response->json('data.0'))->toHaveKeys([
        'id',
        'titulo',
        'nomePaciente',
        'nomeMedico',
        'tipoDocumento',
        'dataDocumento',
        'createdAt',
    ]);
});

test('list documents excludes soft deleted documents', function () {
    // Arrange: Create user and documents
    $user = User::factory()->create();

    Document::factory()->create(['user_id' => $user->id]);

    $trashedDoc = Document::factory()->create(['user_id' => $user->id]);
    $trashedDoc->delete();

    // Act: Get documents list
    $response = $this->actingAs($user)->getJson('/api/v1/documents');

    // Assert: Only active document returned
    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('cannot list documents without authentication', function () {
    // Act: Try to list without authentication
    $response = $this->getJson('/api/v1/documents');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('list documents only shows own documents', function () {
    // Arrange: Create two users and documents
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Document::factory()->create(['user_id' => $user->id]);
    Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Get documents list
    $response = $this->actingAs($user)->getJson('/api/v1/documents');

    // Assert: Only own document returned
    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('returns empty list when user has no documents', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    // Act: Get documents list
    $response = $this->actingAs($user)->getJson('/api/v1/documents');

    // Assert: Empty list
    $response->assertSuccessful()
        ->assertJson(['data' => []]);
});

// ==================== Categories Tests ====================

test('can get document categories', function () {
    // Arrange: Create user and documents with various categories
    $user = User::factory()->create();

    Document::factory()->create([
        'user_id' => $user->id,
        'nome_paciente' => 'João Silva',
        'nome_medico' => 'Dr. Maria',
        'tipo_documento' => 'Exame',
        'titulo' => 'Blood Test',
    ]);

    Document::factory()->create([
        'user_id' => $user->id,
        'nome_paciente' => 'Maria Santos',
        'nome_medico' => 'Dr. João',
        'tipo_documento' => 'Receita',
        'titulo' => 'Prescription',
    ]);

    // Act: Get categories
    $response = $this->actingAs($user)->getJson('/api/v1/documents/categories');

    // Assert: Categories returned
    $response->assertSuccessful();

    $data = $response->json('data');
    expect($data)->toHaveKeys(['pacientes', 'medicos', 'tipos', 'documentos']);
    expect($data['pacientes'])->toContain('João Silva', 'Maria Santos');
    expect($data['medicos'])->toContain('Dr. Maria', 'Dr. João');
    expect($data['tipos'])->toContain('Exame', 'Receita');
    expect($data['documentos'])->toContain('Blood Test', 'Prescription');
});

test('categories excludes soft deleted documents', function () {
    // Arrange: Create user and documents
    $user = User::factory()->create();

    Document::factory()->create([
        'user_id' => $user->id,
        'tipo_documento' => 'Exame',
    ]);

    $trashedDoc = Document::factory()->create([
        'user_id' => $user->id,
        'tipo_documento' => 'Receita',
    ]);
    $trashedDoc->delete();

    // Act: Get categories
    $response = $this->actingAs($user)->getJson('/api/v1/documents/categories');

    // Assert: Only active document categories
    $data = $response->json('data');
    expect($data['tipos'])->toContain('Exame');
    expect($data['tipos'])->not->toContain('Receita');
});

test('categories filters null values', function () {
    // Arrange: Create user and documents with null fields
    $user = User::factory()->create();

    Document::factory()->create([
        'user_id' => $user->id,
        'nome_paciente' => null,
        'nome_medico' => 'Dr. Maria',
    ]);

    // Act: Get categories
    $response = $this->actingAs($user)->getJson('/api/v1/documents/categories');

    // Assert: Null values filtered out
    $data = $response->json('data');
    expect($data['pacientes'])->toBeEmpty();
    expect($data['medicos'])->toContain('Dr. Maria');
});

test('cannot get categories without authentication', function () {
    // Act: Try to get categories without authentication
    $response = $this->getJson('/api/v1/documents/categories');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('categories only shows own document data', function () {
    // Arrange: Create two users and documents
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Document::factory()->create([
        'user_id' => $user->id,
        'tipo_documento' => 'Exame',
    ]);

    Document::factory()->create([
        'user_id' => $otherUser->id,
        'tipo_documento' => 'Receita',
    ]);

    // Act: Get categories
    $response = $this->actingAs($user)->getJson('/api/v1/documents/categories');

    // Assert: Only own categories
    $data = $response->json('data');
    expect($data['tipos'])->toContain('Exame');
    expect($data['tipos'])->not->toContain('Receita');
});

// ==================== Show Tests ====================

test('can view document details', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Test Document',
        'nome_paciente' => 'João Silva',
        'nome_medico' => 'Dr. Maria',
        'tipo_documento' => 'Exame',
        'data_documento' => '2025-01-15',
    ]);

    // Act: Get document details
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Correct data returned
    $response->assertSuccessful()
        ->assertJson([
            'id' => $document->id,
            'titulo' => 'Test Document',
            'nomePaciente' => 'João Silva',
            'nomeMedico' => 'Dr. Maria',
            'tipoDocumento' => 'Exame',
            'dataDocumento' => '2025-01-15',
            'caminhoArquivo' => $document->caminho_arquivo,
        ]);

    // Assert: deletedAt is null for active document
    expect($response->json('deletedAt'))->toBeNull();
});

test('cannot view document without authentication', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to view without authentication
    $response = $this->getJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot view another user document', function () {
    // Arrange: Create two users and document
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to view other user's document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot view soft deleted document', function () {
    // Arrange: Create user and delete document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);
    $document->delete();

    // Act: Try to view deleted document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Not found (soft deleted documents are excluded by default query)
    $response->assertNotFound();
});

test('returns 404 for non-existent document', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    // Act: Try to view non-existent document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/non-existent-uuid');

    // Assert: Not found
    $response->assertNotFound();
});

// ==================== Update Tests ====================

test('can update document with all fields', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Old Title',
        'nome_paciente' => 'Old Patient',
    ]);

    // Act: Update document
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'titulo' => 'New Title',
            'nomePaciente' => 'New Patient',
            'nomeMedico' => 'New Doctor',
            'tipoDocumento' => 'Receita',
            'dataDocumento' => '2025-02-20',
        ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Database updated
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'titulo' => 'New Title',
        'nome_paciente' => 'New Patient',
        'nome_medico' => 'New Doctor',
        'tipo_documento' => 'Receita',
        'data_documento' => '2025-02-20',
    ]);
});

test('can update document with partial fields', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Original Title',
        'nome_paciente' => 'Original Patient',
    ]);

    // Act: Update only title
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'titulo' => 'Updated Title',
        ]);

    // Assert: Title updated, other fields unchanged
    $response->assertSuccessful();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'titulo' => 'Updated Title',
        'nome_paciente' => 'Original Patient',
    ]);
});

test('cannot update document without authentication', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to update without authentication
    $response = $this->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
        'titulo' => 'New Title',
    ]);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot update another user document', function () {
    // Arrange: Create two users and document
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to update other user's document
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'titulo' => 'New Title',
        ]);

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot update soft deleted document', function () {
    // Arrange: Create user and delete document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);
    $document->delete();

    // Act: Try to update deleted document
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'titulo' => 'New Title',
        ]);

    // Assert: Not found (soft deleted documents are excluded by default query)
    $response->assertNotFound();
});

test('validates date format on update', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to update with invalid date
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'dataDocumento' => '15/01/2025', // Wrong format
        ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dataDocumento']);
});

test('validates string max length on update', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to update with too long string
    $response = $this->actingAs($user)
        ->putJson('/api/v1/documents/'.$document->caminho_arquivo, [
            'titulo' => str_repeat('a', 256), // Over 255 chars
        ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['titulo']);
});

// ==================== Destroy Tests ====================

test('can soft delete document', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Document to Delete',
    ]);

    // Act: Delete document
    $response = $this->actingAs($user)
        ->deleteJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Document is soft deleted
    $this->assertSoftDeleted('documents', ['id' => $document->id]);
});

test('cannot delete document without authentication', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to delete without authentication
    $response = $this->deleteJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot delete another user document', function () {
    // Arrange: Create two users and document
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to delete other user's document
    $response = $this->actingAs($user)
        ->deleteJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Not found
    $response->assertNotFound();
});

test('returns 404 when deleting non-existent document', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    // Act: Try to delete non-existent document
    $response = $this->actingAs($user)
        ->deleteJson('/api/v1/documents/non-existent-uuid');

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot delete already soft deleted document', function () {
    // Arrange: Create user and delete document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);
    $document->delete();

    // Act: Try to delete again
    $response = $this->actingAs($user)
        ->deleteJson('/api/v1/documents/'.$document->caminho_arquivo);

    // Assert: Not found (soft deleted documents are excluded by default query)
    $response->assertNotFound();
});

// ==================== Download Tests ====================

test('can download document', function () {
    // Arrange: Create user and mock file storage
    $user = User::factory()->create();

    $fileStorage = mock(FileStoragePort::class);
    $fileStorage->shouldReceive('retrieve')
        ->once()
        ->andReturn('fake-pdf-content');

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Test Document',
    ]);

    // Act: Download document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo.'/download');

    // Assert: Successful with PDF headers
    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))
        ->toContain('inline')
        ->toContain('Test Document.pdf');
});

test('cannot download document without authentication', function () {
    // Arrange: Create user and document
    $user = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to download without authentication
    $response = $this->getJson('/api/v1/documents/'.$document->caminho_arquivo.'/download');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot download another user document', function () {
    // Arrange: Create two users and document
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to download other user's document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo.'/download');

    // Assert: Not found
    $response->assertNotFound();
});

test('returns error when file retrieval fails on download', function () {
    // Arrange: Create user and mock file storage to fail
    $user = User::factory()->create();

    $fileStorage = mock(FileStoragePort::class);
    $fileStorage->shouldReceive('retrieve')
        ->once()
        ->andReturn(false);

    $document = Document::factory()->create(['user_id' => $user->id]);

    // Act: Try to download
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/'.$document->caminho_arquivo.'/download');

    // Assert: Error response
    $response->assertStatus(500)
        ->assertJson(['message' => 'unexpected_error']);
});

test('returns 404 when downloading non-existent document', function () {
    // Arrange: Create user
    $user = User::factory()->create();

    // Act: Try to download non-existent document
    $response = $this->actingAs($user)
        ->getJson('/api/v1/documents/non-existent-uuid/download');

    // Assert: Not found
    $response->assertNotFound();
});

// ==================== Export Tests ====================

test('can export documents via email', function () {
    // Arrange: Create user, mock mail, and create fake export file
    $user = User::factory()->create();

    Mail::fake();

    $exportPath = base_path('export.zip');
    file_put_contents($exportPath, 'fake-zip-content');

    // Act: Request export
    $response = $this->actingAs($user)
        ->postJson('/api/v1/documents/export');

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Email sent with ExportEmail mailable
    Mail::assertSent(\App\Modules\Document\Mail\ExportEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    // Cleanup
    if (file_exists($exportPath)) {
        unlink($exportPath);
    }
});

test('cannot export without authentication', function () {
    // Act: Try to export without authentication
    $response = $this->postJson('/api/v1/documents/export');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('returns error when export file does not exist', function () {
    // Arrange: Create user and ensure export file doesn't exist
    $user = User::factory()->create();

    $exportPath = base_path('export.zip');
    if (file_exists($exportPath)) {
        unlink($exportPath);
    }

    // Act: Request export
    $response = $this->actingAs($user)
        ->postJson('/api/v1/documents/export');

    // Assert: Error response
    $response->assertStatus(500)
        ->assertJson(['message' => 'unexpected_error']);
});
