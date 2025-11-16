<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Exceptions\ApiException;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use Illuminate\Http\JsonResponse;

class TrashController extends Controller
{
    public function __construct(
        protected FileStoragePort $fileStorage
    ) {}

    /**
     * Display a listing of trashed documents.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $documents = Document::where('user_id', $user->id)
            ->onlyTrashed()
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'titulo' => $doc->titulo,
                    'nomePaciente' => $doc->nome_paciente,
                    'nomeMedico' => $doc->nome_medico,
                    'tipoDocumento' => $doc->tipo_documento,
                    'dataDocumento' => $doc->data_documento?->format('Y-m-d'),
                    'createdAt' => $doc->created_at->format('Y-m-d'),
                    'deletedAt' => $doc->deleted_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'data' => $documents,
        ]);
    }

    /**
     * Display the specified trashed document.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('id', $id)
            ->where('user_id', $user->id)
            ->onlyTrashed()
            ->firstOrFail();

        return response()->json([
            'id' => $document->id,
            'titulo' => $document->titulo,
            'nomePaciente' => $document->nome_paciente,
            'nomeMedico' => $document->nome_medico,
            'tipoDocumento' => $document->tipo_documento,
            'dataDocumento' => $document->data_documento?->format('Y-m-d'),
            'createdAt' => $document->created_at->format('Y-m-d'),
            'deletedAt' => $document->deleted_at->format('Y-m-d'),
        ]);
    }

    /**
     * Restore the specified document from trash.
     */
    public function restore(string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('id', $id)
            ->where('user_id', $user->id)
            ->onlyTrashed()
            ->firstOrFail();

        $document->restore();

        return response()->json([]);
    }

    /**
     * Permanently remove the specified document from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('id', $id)
            ->where('user_id', $user->id)
            ->onlyTrashed()
            ->firstOrFail();

        // Delete the physical file
        $deleted = $this->fileStorage->delete(
            (string) $user->id,
            $document->id
        );

        if (! $deleted) {
            $error = ApiException::unexpectedError();

            return response()->json(['message' => $error->message], $error->code);
        }

        // Permanently delete the document record
        $document->forceDelete();

        return response()->json([]);
    }
}
