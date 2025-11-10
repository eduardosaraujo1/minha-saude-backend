<?php

use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use App\Modules\User\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('can list trashed documents', function () {
    // Arrange: Create documents (one active, two trashed)
    $activeDoc = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Active Document',
    ]);

    $trashedDoc1 = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Trashed Document 1',
    ]);
    $trashedDoc1->delete();

    $trashedDoc2 = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Trashed Document 2',
    ]);
    $trashedDoc2->delete();

    // Act: Get trashed documents list
    $response = $this->actingAs($this->user)->getJson('/api/v1/trash');

    // Assert: Only trashed documents are returned
    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['titulo' => 'Trashed Document 1'])
        ->assertJsonFragment(['titulo' => 'Trashed Document 2'])
        ->assertJsonMissing(['titulo' => 'Active Document']);

    // Assert: Response structure
    expect($response->json('data.0'))->toHaveKeys([
        'id',
        'titulo',
        'nomePaciente',
        'nomeMedico',
        'tipoDocumento',
        'dataDocumento',
        'createdAt',
        'deletedAt',
    ]);
});

test('cannot list trash without authentication', function () {
    // Act: Try to get trash without authentication
    $response = $this->getJson('/api/v1/trash');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('list trash only shows own documents', function () {
    // Arrange: Create documents for two users
    $otherUser = User::factory()->create();

    $myDoc = Document::factory()->create(['user_id' => $this->user->id]);
    $myDoc->delete();

    $otherDoc = Document::factory()->create(['user_id' => $otherUser->id]);
    $otherDoc->delete();

    // Act: Get trash list
    $response = $this->actingAs($this->user)->getJson('/api/v1/trash');

    // Assert: Only own documents are shown
    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('can view trashed document details', function () {
    // Arrange: Create and trash a document
    $document = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Test Document',
        'nome_paciente' => 'João Silva',
        'nome_medico' => 'Dr. Maria',
        'tipo_documento' => 'Exame',
        'data_documento' => '2025-01-15',
    ]);
    $document->delete();

    // Act: Get document details
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/trash/'.$document->caminho_arquivo);

    // Assert: Correct data is returned
    $response->assertSuccessful()
        ->assertJson([
            'idDocumento' => $document->id,
            'titulo' => 'Test Document',
            'nomePaciente' => 'João Silva',
            'nomeMedico' => 'Dr. Maria',
            'tipoDocumento' => 'Exame',
            'dataDocumento' => '2025-01-15',
            'caminhoArquivo' => $document->caminho_arquivo,
        ]);

    // Assert: deletedAt is present
    expect($response->json('deletedAt'))->not->toBeNull();
});

test('cannot view trashed document without authentication', function () {
    // Arrange: Create and trash a document
    $document = Document::factory()->create(['user_id' => $this->user->id]);
    $document->delete();

    // Act: Try to view without authentication
    $response = $this->getJson('/api/v1/trash/'.$document->caminho_arquivo);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot view another user trashed document', function () {
    // Arrange: Create document for another user
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);
    $document->delete();

    // Act: Try to view other user's document
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/trash/'.$document->caminho_arquivo);

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot view non-trashed document in trash', function () {
    // Arrange: Create active document
    $document = Document::factory()->create(['user_id' => $this->user->id]);

    // Act: Try to view as trash
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/trash/'.$document->caminho_arquivo);

    // Assert: Not found
    $response->assertNotFound();
});

test('can restore trashed document', function () {
    // Arrange: Create and trash a document
    $document = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Document to Restore',
    ]);
    $document->delete();

    // Assert: Document is trashed
    $this->assertSoftDeleted('documents', ['id' => $document->id]);

    // Act: Restore the document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/restore');

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Document is no longer trashed
    $this->assertNotSoftDeleted('documents', ['id' => $document->id]);
});

test('cannot restore trashed document without authentication', function () {
    // Arrange: Create and trash a document
    $document = Document::factory()->create(['user_id' => $this->user->id]);
    $document->delete();

    // Act: Try to restore without authentication
    $response = $this->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/restore');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot restore another user trashed document', function () {
    // Arrange: Create document for another user
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);
    $document->delete();

    // Act: Try to restore other user's document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/restore');

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot restore non-trashed document', function () {
    // Arrange: Create active document
    $document = Document::factory()->create(['user_id' => $this->user->id]);

    // Act: Try to restore active document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/restore');

    // Assert: Not found
    $response->assertNotFound();
});

test('can permanently delete trashed document', function () {
    // Arrange: Mock the file storage
    $fileStorage = $this->mock(FileStoragePort::class);
    $fileStorage->shouldReceive('delete')
        ->once()
        ->andReturn(true);

    // Create and trash a document
    $document = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Document to Delete',
    ]);
    $document->delete();

    // Assert: Document exists in trash
    $this->assertSoftDeleted('documents', ['id' => $document->id]);

    // Act: Permanently delete the document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/destroy');

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Document is permanently deleted
    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
});

test('cannot permanently delete trashed document without authentication', function () {
    // Arrange: Create and trash a document
    $document = Document::factory()->create(['user_id' => $this->user->id]);
    $document->delete();

    // Act: Try to delete without authentication
    $response = $this->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/destroy');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot permanently delete another user trashed document', function () {
    // Arrange: Create document for another user
    $otherUser = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $otherUser->id]);
    $document->delete();

    // Act: Try to delete other user's document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/destroy');

    // Assert: Not found
    $response->assertNotFound();
});

test('cannot permanently delete non-trashed document', function () {
    // Arrange: Create active document
    $document = Document::factory()->create(['user_id' => $this->user->id]);

    // Act: Try to permanently delete active document
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/destroy');

    // Assert: Not found
    $response->assertNotFound();
});

test('returns error when file deletion fails', function () {
    // Arrange: Mock the file storage to fail
    $fileStorage = $this->mock(FileStoragePort::class);
    $fileStorage->shouldReceive('delete')
        ->once()
        ->andReturn(false);

    // Create and trash a document
    $document = Document::factory()->create(['user_id' => $this->user->id]);
    $document->delete();

    // Act: Try to permanently delete
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/trash/'.$document->caminho_arquivo.'/destroy');

    // Assert: Error response
    $response->assertStatus(500)
        ->assertJson(['message' => 'unexpected_error']);

    // Assert: Document still exists in database
    $this->assertSoftDeleted('documents', ['id' => $document->id]);
});
