@props(['document', 'shareCode' => ''])

@php
    $patient = $document->nome_paciente ?? __('Não informado');
    $doctor = $document->nome_medico ?? __('Não informado');
    $type = $document->tipo_documento ?? __('Não informado');
    $performedAt = $document->data_documento
        ? $document->data_documento->format('d/m/Y')
        : __('Data não disponível');
    $downloadUrl = route('compartilhados.download', [
        'document' => $document->id,
        'code' => $shareCode,
    ]);
@endphp

<article
    class="flex flex-col gap-4 rounded-2xl border border-[#dfe0e5] bg-white/95 p-5 shadow-sm backdrop-blur-md transition hover:border-[#b8bbc7] hover:shadow-md dark:border-[#2f3037] dark:bg-[#1b1c1f] dark:hover:border-[#4f525d]"
    aria-label="Documento {{ $document->titulo }}">
    <div class="flex items-start gap-4">
        <span class="material-symbols-rounded text-4xl text-[#2c3d63] dark:text-[#9db2f8]"
            aria-hidden="true">description</span>
        <div class="flex flex-col gap-1">
            <h3 class="text-lg font-semibold text-[#111322] dark:text-white">
                {{ $document->titulo ?? __('Documento sem título') }}</h3>
            <p class="text-sm text-[#5f6473] dark:text-[#a0a5b8]">{{ __('Código: :code', ['code' => $shareCode]) }}</p>
        </div>
    </div>

    <dl class="grid grid-cols-1 gap-3 text-sm text-[#1f2433] dark:text-[#d2d7eb]">
        <div>
            <dt class="font-semibold text-[#5f6473] dark:text-[#9aa0ba]">{{ __('Paciente') }}</dt>
            <dd>{{ $patient }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-[#5f6473] dark:text-[#9aa0ba]">{{ __('Tipo de documento') }}</dt>
            <dd>{{ $type }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-[#5f6473] dark:text-[#9aa0ba]">{{ __('Doutor(a) responsável') }}</dt>
            <dd>{{ $doctor }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-[#5f6473] dark:text-[#9aa0ba]">{{ __('Data de realização') }}</dt>
            <dd>{{ $performedAt }}</dd>
        </div>
    </dl>

    <div class="mt-2 flex justify-end">
        <a href="{{ $downloadUrl }}"
            class="inline-flex items-center gap-2 rounded-full border border-transparent bg-[#1f2433] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#111322] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1f2433] dark:bg-[#7283ff] dark:hover:bg-[#5665d0]">
            <span class="material-symbols-rounded text-base" aria-hidden="true">download</span>
            {{ __('Baixar PDF') }}
        </a>
    </div>
</article>
