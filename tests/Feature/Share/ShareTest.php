<?php

use App\Modules\Document\Models\Document;
use App\Modules\Share\Models\Share;
use App\Modules\User\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('can create share with valid documents', function () {
    // Arrange: Create documents for user
    $doc1 = Document::factory()->create(['user_id' => $this->user->id]);
    $doc2 = Document::factory()->create(['user_id' => $this->user->id]);

    // Act: Create share
    $response = $this->actingAs($this->user)->postJson('/api/v1/shares', [
        'idsDocumentos' => [$doc1->id, $doc2->id],
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Share was created in database
    $this->assertDatabaseHas('shares', [
        'user_id' => $this->user->id,
    ]);

    // Assert: Documents are attached to share
    $share = Share::where('user_id', $this->user->id)->first();
    expect($share->documents)->toHaveCount(2);
    expect($share->documents->pluck('id')->toArray())->toContain($doc1->id, $doc2->id);
});

test('cannot create share without authentication', function () {
    // Arrange: Create a document
    $doc = Document::factory()->create();

    // Act: Try to create share without auth
    $response = $this->postJson('/api/v1/shares', [
        'idsDocumentos' => [$doc->id],
    ]);

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot create share without documents', function () {
    // Act: Try to create share without documents
    $response = $this->actingAs($this->user)->postJson('/api/v1/shares', [
        'idsDocumentos' => [],
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['idsDocumentos']);
});

test('cannot create share with missing idsDocumentos field', function () {
    // Act: Try to create share without field
    $response = $this->actingAs($this->user)->postJson('/api/v1/shares', []);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['idsDocumentos']);
});

test('cannot create share with non-existent documents', function () {
    // Act: Try to create share with invalid document ID
    $response = $this->actingAs($this->user)->postJson('/api/v1/shares', [
        'idsDocumentos' => [99999],
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['idsDocumentos.0']);
});

test('cannot create share with documents from another user', function () {
    // Arrange: Create document for another user
    $otherUser = User::factory()->create();
    $doc = Document::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to create share with other user's document
    $response = $this->actingAs($this->user)->postJson('/api/v1/shares', [
        'idsDocumentos' => [$doc->id],
    ]);

    // Assert: Forbidden
    $response->assertStatus(403)
        ->assertJson(['message' => 'forbidden_document_access']);
});

test('generates unique 8-character share code', function () {
    // Arrange: Create documents
    $doc = Document::factory()->create(['user_id' => $this->user->id]);

    // Act: Create share
    $this->actingAs($this->user)->postJson('/api/v1/shares', [
        'idsDocumentos' => [$doc->id],
    ]);

    // Assert: Share code exists and has correct format
    $share = Share::where('user_id', $this->user->id)->first();
    expect($share->codigo)->toHaveLength(8);
    expect($share->codigo)->toMatch('/^[A-Z0-9]{8}$/');
});

test('can list active share codes', function () {
    // Arrange: Create shares for user (ensure they are not expired by not setting data_primeiro_uso)
    $doc1 = Document::factory()->create(['user_id' => $this->user->id]);
    $doc2 = Document::factory()->create(['user_id' => $this->user->id]);

    $share1 = Share::factory()->create([
        'user_id' => $this->user->id,
        'data_primeiro_uso' => null,
    ]);
    $share1->documents()->attach($doc1->id);

    $share2 = Share::factory()->create([
        'user_id' => $this->user->id,
        'data_primeiro_uso' => null,
    ]);
    $share2->documents()->attach($doc2->id);

    // Act: Get shares list
    $response = $this->actingAs($this->user)->getJson('/api/v1/shares');

    // Assert: Only active shares are returned
    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['codigo' => $share1->codigo])
        ->assertJsonFragment(['codigo' => $share2->codigo]);

    // Assert: Response structure
    expect($response->json('data.0'))->toHaveKeys([
        'id',
        'codigo',
        'expiresAt',
    ]);
});

test('cannot list shares without authentication', function () {
    // Act: Try to get shares without auth
    $response = $this->getJson('/api/v1/shares');

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('list shares only shows own shares', function () {
    // Arrange: Create shares for two users (ensure they are not expired)
    $otherUser = User::factory()->create();

    $myDoc = Document::factory()->create(['user_id' => $this->user->id]);
    $myShare = Share::factory()->create([
        'user_id' => $this->user->id,
        'data_primeiro_uso' => null,
    ]);
    $myShare->documents()->attach($myDoc->id);

    $otherDoc = Document::factory()->create(['user_id' => $otherUser->id]);
    $otherShare = Share::factory()->create([
        'user_id' => $otherUser->id,
        'data_primeiro_uso' => null,
    ]);
    $otherShare->documents()->attach($otherDoc->id);

    // Act: Get shares list
    $response = $this->actingAs($this->user)->getJson('/api/v1/shares');

    // Assert: Only own shares are shown
    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['codigo' => $myShare->codigo])
        ->assertJsonMissing(['codigo' => $otherShare->codigo]);
});

test('can view share details', function () {
    // Arrange: Create share with documents
    $doc1 = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Document 1',
    ]);
    $doc2 = Document::factory()->create([
        'user_id' => $this->user->id,
        'titulo' => 'Document 2',
    ]);

    $share = Share::factory()->create([
        'user_id' => $this->user->id,
        'data_primeiro_uso' => null,
    ]);
    $share->documents()->attach([$doc1->id, $doc2->id]);

    // Act: Get share details
    $response = $this->actingAs($this->user)->getJson("/api/v1/shares/{$share->codigo}");

    // Assert: Correct data is returned
    $response->assertSuccessful()
        ->assertJsonFragment(['codigo' => $share->codigo])
        ->assertJsonStructure([
            'id',
            'codigo',
            'expiresAt',
            'documentos' => [
                '*' => ['id', 'titulo', 'nomePaciente', 'nomeMedico', 'tipoDocumento', 'dataDocumento'],
            ],
        ]);

    // Assert documents are included
    expect($response->json('documentos'))->toHaveCount(2);
    $documentIds = collect($response->json('documentos'))->pluck('id')->toArray();
    expect($documentIds)->toContain($doc1->id, $doc2->id);
});

test('cannot view share details without authentication', function () {
    // Arrange: Create share
    $share = Share::factory()->create(['user_id' => $this->user->id]);

    // Act: Try to view without auth
    $response = $this->getJson("/api/v1/shares/{$share->codigo}");

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot view another user share details', function () {
    // Arrange: Create share for another user
    $otherUser = User::factory()->create();
    $share = Share::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to view other user's share
    $response = $this->actingAs($this->user)->getJson("/api/v1/shares/{$share->codigo}");

    // Assert: Not found
    $response->assertNotFound();
});

test('can delete share', function () {
    // Arrange: Create share
    $doc = Document::factory()->create(['user_id' => $this->user->id]);
    $share = Share::factory()->create(['user_id' => $this->user->id]);
    $share->documents()->attach($doc->id);

    // Act: Delete share
    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/shares/{$share->codigo}");

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Share is deleted from database
    $this->assertDatabaseMissing('shares', ['id' => $share->id]);
});

test('cannot delete share without authentication', function () {
    // Arrange: Create share
    $share = Share::factory()->create(['user_id' => $this->user->id]);

    // Act: Try to delete without auth
    $response = $this->deleteJson("/api/v1/shares/{$share->codigo}");

    // Assert: Unauthorized
    $response->assertUnauthorized();
});

test('cannot delete another user share', function () {
    // Arrange: Create share for another user
    $otherUser = User::factory()->create();
    $share = Share::factory()->create(['user_id' => $otherUser->id]);

    // Act: Try to delete other user's share
    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/shares/{$share->codigo}");

    // Assert: Not found
    $response->assertNotFound();
});

test('deleting share removes pivot table entries', function () {
    // Arrange: Create share with documents
    $doc1 = Document::factory()->create(['user_id' => $this->user->id]);
    $doc2 = Document::factory()->create(['user_id' => $this->user->id]);
    $share = Share::factory()->create(['user_id' => $this->user->id]);
    $share->documents()->attach([$doc1->id, $doc2->id]);

    // Assert: Pivot entries exist
    $this->assertDatabaseHas('document_share', ['share_id' => $share->id, 'document_id' => $doc1->id]);
    $this->assertDatabaseHas('document_share', ['share_id' => $share->id, 'document_id' => $doc2->id]);

    // Act: Delete share
    $this->actingAs($this->user)->deleteJson("/api/v1/shares/{$share->codigo}");

    // Assert: Pivot entries are removed (cascade delete)
    $this->assertDatabaseMissing('document_share', ['share_id' => $share->id]);
});

test('share can have data_primeiro_uso set', function () {
    // Arrange: Create share with primeiro uso date
    $doc = Document::factory()->create(['user_id' => $this->user->id]);
    $share = Share::factory()->create([
        'user_id' => $this->user->id,
        'data_primeiro_uso' => now()->subDays(5),
    ]);
    $share->documents()->attach($doc->id);

    // Act: Get share details
    $response = $this->actingAs($this->user)->getJson("/api/v1/shares/{$share->codigo}");

    // Assert: expiresAt is returned
    $response->assertSuccessful();
    expect($response->json('expiresAt'))->not->toBeNull();
});
