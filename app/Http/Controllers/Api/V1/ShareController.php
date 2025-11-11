<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreShareRequest;
use App\Modules\Share\Models\Share;
use Illuminate\Http\JsonResponse;

class ShareController extends Controller
{
    /**
     * Display a listing of active share codes.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $shares = Share::where('user_id', $user->id)
            ->where('expirado', false)
            ->get()
            ->map(function ($share) {
                return [
                    'data' => [
                        'id' => $share->id,
                        'codigo' => $share->codigo,
                        'dataPrimeiroUso' => $share->data_primeiro_uso?->format('Y-m-d'),
                        'expirado' => $share->expirado,
                        'createdAt' => $share->created_at->format('Y-m-d'),
                    ],
                ];
            });

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

        // Verify all documents belong to the user
        $userDocuments = $user->documents()
            ->whereIn('id', $documentIds)
            ->pluck('id')
            ->toArray();

        if (count($userDocuments) !== count($documentIds)) {
            return response()->json([
                'message' => 'Some documents do not belong to you',
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
            ];
        });

        return response()->json([
            'codigo' => $share->codigo,
            'primeiroUsoEm' => $share->data_primeiro_uso?->format('Y-m-d'),
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
            $timestamp = now()->timestamp;
            $random = random_int(1000, 9999);
            $code = strtoupper(substr("SHARE{$timestamp}{$random}", -8));
        } while (Share::where('codigo', $code)->exists());

        return $code;
    }
}
