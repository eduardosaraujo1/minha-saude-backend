<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Exceptions\ApiException;
use App\Http\Requests\V1\StoreDocumentRequest;
use App\Http\Requests\V1\UpdateDocumentRequest;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct(
        protected FileStoragePort $fileStorage
    ) {}

    /**
     * Upload new document(s).
     */
    public function upload(StoreDocumentRequest $request): JsonResponse
    {
        $user = auth()->user();
        $file = $request->file('arquivos');

        $uuid = (string) Str::uuid();

        // Store the file using the FileStoragePort
        $stored = $this->fileStorage->store(
            (string) $user->id,
            $uuid,
            $file
        );

        if (! $stored) {
            $error = ApiException::unexpectedError();

            return response()->json(['message' => $error->message], $error->code);
        }

        // Create document record
        Document::create([
            'titulo' => $request->input('titulo', 'Documento sem título'),
            'nome_paciente' => $request->input('nomePaciente'),
            'nome_medico' => $request->input('nomeMedico'),
            'tipo_documento' => $request->input('tipoDocumento'),
            'data_documento' => $request->input('dataDocumento'),
            'caminho_arquivo' => $uuid,
            'user_id' => $user->id,
        ]);

        return response()->json([]);
    }

    /**
     * Download a specific document.
     */
    public function download(string $id): mixed
    {
        $user = auth()->user();

        // Find document by caminho_arquivo (uuid)
        $document = Document::where('caminho_arquivo', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Retrieve file content
        $fileContent = $this->fileStorage->retrieve(
            (string) $user->id,
            $document->caminho_arquivo
        );

        if (! $fileContent) {
            $error = ApiException::unexpectedError();

            return response()->json(['message' => $error->message], $error->code);
        }

        return response($fileContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$document->titulo.'.pdf"');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $documents = Document::where('user_id', $user->id)
            ->whereNull('deleted_at')
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
                ];
            });

        return response()->json([
            'data' => $documents,
        ]);
    }

    /**
     * List pre-existing categories.
     */
    public function categories(): JsonResponse
    {
        $user = auth()->user();

        $documents = Document::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get();

        $pacientes = $documents->pluck('nome_paciente')->filter()->unique()->values();
        $medicos = $documents->pluck('nome_medico')->filter()->unique()->values();
        $tipos = $documents->pluck('tipo_documento')->filter()->unique()->values();
        $documentos = $documents->pluck('titulo')->filter()->unique()->values();

        return response()->json([
            'data' => [
                'pacientes' => $pacientes,
                'medicos' => $medicos,
                'tipos' => $tipos,
                'documentos' => $documentos,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('caminho_arquivo', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($document->deleted_at) {
            $error = ApiException::forbiddenError();

            return response()->json(['message' => $error->message], $error->code);
        }

        return response()->json([
            'id' => $document->id,
            'titulo' => $document->titulo,
            'nomePaciente' => $document->nome_paciente,
            'nomeMedico' => $document->nome_medico,
            'tipoDocumento' => $document->tipo_documento,
            'dataDocumento' => $document->data_documento?->format('Y-m-d'),
            'createdAt' => $document->created_at->format('Y-m-d'),
            'deletedAt' => $document->deleted_at?->format('Y-m-d'),
            'caminhoArquivo' => $document->caminho_arquivo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('caminho_arquivo', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($document->deleted_at) {
            $error = ApiException::forbiddenError();

            return response()->json(['message' => $error->message], $error->code);
        }

        $document->update([
            'titulo' => $request->input('titulo', $document->titulo),
            'nome_paciente' => $request->input('nomePaciente', $document->nome_paciente),
            'nome_medico' => $request->input('nomeMedico', $document->nome_medico),
            'tipo_documento' => $request->input('tipoDocumento', $document->tipo_documento),
            'data_documento' => $request->input('dataDocumento', $document->data_documento),
        ]);

        return response()->json([]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();

        $document = Document::where('caminho_arquivo', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $document->delete();

        return response()->json([]);
    }

    /**
     * Generate data export and send via email.
     */
    public function export(Request $request): JsonResponse
    {
        $user = auth()->user();
        $exportFilePath = base_path('export.zip');

        if (! file_exists($exportFilePath)) {
            $error = ApiException::unexpectedError();

            return response()->json(['message' => $error->message], $error->code);
        }

        Mail::raw('Segue em anexo sua exportação de dados solicitada.', function ($message) use ($user, $exportFilePath) {
            $message->to($user->email)
                ->subject('Exportação de Dados - Minha Saúde')
                ->attach($exportFilePath, [
                    'as' => 'export.zip',
                    'mime' => 'application/zip',
                ]);
        });

        return response()->json([]);
    }
}
