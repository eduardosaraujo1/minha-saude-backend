<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreShareRequest;
use App\Modules\Share\Models\Share;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Str;

class ShareController extends Controller
{
    /**
     * Display a listing of active share codes.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $shares = Share::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->get()
            ->map(function (Share $share) {
                return [
                    'id' => $share->id,
                    'codigo' => $share->codigo,
                    'expiresAt' => $share->expiresAt(),
                ];
            })->filter(fn ($share) => $share['expiresAt'] > now());

        return response()->json([
            'data' => $shares,
        ]);
    }

    /**
     * Store a newly created share in storage.
     */
    public function store(StoreShareRequest $request): JsonResponse
    {
        $user = auth()->user();
        $documentIds = $request->input('idsDocumentos');
        if (! $user instanceof User) {
            return response()->json([
                'message' => 'unauthenticated',
            ], 401);
        }

        // Verify all documents belong to the user
        $userDocuments = $user->documents()
            ->whereIn('id', $documentIds)
            ->pluck('id')
            ->toArray();

        if (count($userDocuments) !== count($documentIds)) {
            return response()->json([
                'message' => 'forbidden_document_access',
            ], 403);
        }

        // Generate unique share code
        $codigo = $this->generateShareCode();

        // Create share record
        $share = Share::create([
            'codigo' => $codigo,
            'user_id' => $user->id,
            'expirado' => false,
        ]);

        // Attach documents to share
        $share->documents()->attach($documentIds);

        return response()->json([]);
    }

    /**
     * Display the specified share by code.
     */
    public function show(string $code): JsonResponse
    {
        $user = auth()->user();

        $share = Share::where('codigo', $code)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $documentos = $share->documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'titulo' => $doc->titulo,
                'nomePaciente' => $doc->nome_paciente,
                'nomeMedico' => $doc->nome_medico,
                'tipoDocumento' => $doc->tipo_documento,
                'dataDocumento' => $doc->data_documento,
            ];
        });

        return response()->json([
            'id' => $share->id,
            'codigo' => $share->codigo,
            'expiresAt' => $share->expiresAt(),
            'documentos' => $documentos,
        ]);
    }

    /**
     * Remove the specified share from storage.
     */
    public function destroy(string $code): JsonResponse
    {
        $user = auth()->user();

        $share = Share::where('codigo', $code)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $share->delete();

        return response()->json([]);
    }

    /**
     * Generate a unique 8-character share code.
     */
    protected function generateShareCode(): string
    {
        do {
            $random = Str::random(8);
            $code = strtoupper(substr("SHARE{$random}", -8));
        } while (Share::where('codigo', $code)->exists());

        return Str::upper($code);
    }
}
