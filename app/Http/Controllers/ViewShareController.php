<?php

namespace App\Http\Controllers;

use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\Ports\FileStoragePort;
use App\Modules\Share\Models\Share;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ViewShareController extends Controller
{
    public function __construct(public FileStoragePort $fileStorage) {}

    public function index(Request $request): View|Response
    {
        $code = $this->normalizeShareCode($request->query('code'));
        $isHtmx = $this->isHtmxRequest($request);

        [$share, $error] = $this->resolveShareByCode($code, $isHtmx);

        $documents = $share?->documents ?? collect();
        $viewData = [
            'documents' => $documents,
            'shareCode' => $code,
            'error' => $error,
        ];

        if ($isHtmx) {
            $status = $error ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK;

            return response()->view('compartilhados.partials.partial-thing', $viewData, $status);
        }

        return view('compartilhados.index', $viewData);
    }

    public function download(Request $request, Document $document): Response
    {
        $code = $this->normalizeShareCode($request->query('code'));
        [$share, $error] = $this->resolveShareByCode($code, true);

        if ($error || ! $share) {
            abort(404);
        }

        if (! $share->documents->contains(fn (Document $doc) => $doc->id === $document->id)) {
            abort(404);
        }

        $fileContent = $this->fileStorage->retrieve((string) $share->user_id, (string) $document->id);

        if ($fileContent === null) {
            abort(404);
        }

        $filename = Str::slug($document->titulo ?: 'documento').'.pdf';

        return response($fileContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array{Share|null, string|null}
     */
    protected function resolveShareByCode(?string $code, bool $codeIsRequired = false): array
    {
        if ($code === '' || $code === null) {
            return [null, $codeIsRequired ? __('Informe um código para consultar os documentos.') : null];
        }

        if (! preg_match('/^[A-Z0-9]{8}$/', $code)) {
            return [null, __('Código inválido. Utilize 8 caracteres alfanuméricos.')];
        }

        $share = Share::query()
            ->with(['documents' => fn ($query) => $query->orderBy('titulo')->latest('created_at')])
            ->where('codigo', $code)
            ->first();

        if (! $share) {
            return [null, __('Compartilhamento não encontrado.')];
        }

        if ($share->expiresAt()->isPast()) {
            return [null, __('Este compartilhamento expirou. Solicite um novo código.')];
        }

        if ($share->data_primeiro_uso === null) {
            $share->forceFill(['data_primeiro_uso' => now()])->save();
        }

        return [$share, null];
    }

    protected function normalizeShareCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    protected function isHtmxRequest(Request $request): bool
    {
        return (bool) $request->header('HX-Request');
    }
}
