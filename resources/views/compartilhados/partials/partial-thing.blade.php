<div>
    @if ($error)
        <div
            class="rounded-2xl border border-[#f6c6c8] bg-[#fff1f2] px-5 py-4 text-sm text-[#7a1f23] dark:border-[#4b1f23] dark:bg-[#2c0f12] dark:text-[#f8d7da]">
            {{ $error }}
        </div>
    @elseif ($documents->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-[#c6c9d4] px-5 py-8 text-center text-sm text-[#5f6473] dark:border-[#3b3d46] dark:text-[#a0a5b8]">
            {{ __('Nenhum documento disponível para este compartilhamento.') }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($documents as $document)
                <x-compartilhados::document-card :document="$document" :share-code="$shareCode" />
            @endforeach
        </div>
    @endif
</div>
