<?php

use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use App\Modules\Share\Models\Share;
use App\Modules\User\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\mock;

it('renders the compartilhados landing page', function () {
    $response = $this->get('/compartilhados');

    $response->assertOk()
        ->assertSee('Visualizar Compartilhamento');
});

it('requires a share code for HTMX requests', function () {
    $response = $this->get('/compartilhados', [
        'HX-Request' => 'true',
    ]);

    $response->assertStatus(422)
        ->assertSee('Informe um código');
});

it('lists documents for a valid share code', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Exame de Sangue',
    ]);

    $share = Share::factory()->create([
        'codigo' => 'ABCD1234',
        'data_primeiro_uso' => null,
        'user_id' => $user->id,
    ]);
    $share->documents()->attach($document->id);

    $response = $this->get('/compartilhados?code=abcd1234', [
        'HX-Request' => 'true',
    ]);

    $response->assertOk()
        ->assertSee('Exame de Sangue');

    expect($share->fresh()->data_primeiro_uso)->not()->toBeNull();
});

it('prevents access when the share is expired', function () {
    Carbon::setTestNow(now());
    $user = User::factory()->create();
    $share = Share::factory()->create([
        'codigo' => 'ZXCV5678',
        'user_id' => $user->id,
        'data_primeiro_uso' => null,
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->get('/compartilhados?code=ZXCV5678', [
        'HX-Request' => 'true',
    ]);

    $response->assertStatus(422)
        ->assertSee('expirou');

    Carbon::setTestNow();
});

it('downloads a document when the code and document match', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'user_id' => $user->id,
        'titulo' => 'Relatorio Final',
    ]);
    $share = Share::factory()->create([
        'codigo' => 'RELATO12',
        'user_id' => $user->id,
        'data_primeiro_uso' => now(),
    ]);
    $share->documents()->attach($document->id);

    mock(FileStoragePort::class, function ($mock) use ($user, $document) {
        $mock->shouldReceive('retrieve')
            ->once()
            ->with((string) $user->id, (string) $document->id)
            ->andReturn('%PDF-1.4 sample%');
    });

    $response = $this->get("/compartilhados/{$document->id}?code=relato12");

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'attachment; filename="relatorio-final.pdf"')
        ->assertSee('%PDF-1.4 sample%', false);
});
