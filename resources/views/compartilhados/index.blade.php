<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Visualizar Compartilhamento') }} • {{ config('app.name') }}</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" />
    <style>
        #share-loading-indicator {
            display: none;
        }

        #share-loading-indicator.htmx-request {
            display: inline-flex;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f4f6fb] text-[#111322] antialiased dark:bg-[#090b13] dark:text-white">
    <div class="flex min-h-screen flex-col">
        <main class="flex-1">
            <section class="mx-auto w-full max-w-6xl px-6">
                <div class="flex flex-col gap-3">
                    <h1 class="text-3xl font-semibold text-[#101225] dark:text-white">
                        {{ __('Visualizar Compartilhamento') }}
                    </h1>
                    <p class="text-base text-[#5f6473] dark:text-[#b5bad3]">
                        {{ __('Consulte documentos compartilhados inserindo o código enviado pelo paciente.') }}
                    </p>
                </div>

                <form id="share-search-form" method="GET" action="{{ route('compartilhados.index') }}"
                    hx-get="{{ route('compartilhados.index') }}" hx-target="#share-document-list" hx-swap="innerHTML"
                    hx-push-url="true" hx-indicator="#share-loading-indicator"
                    class="mt-10 flex flex-col gap-4 rounded-3xl border border-[#dee1f0] bg-white/80 p-6 shadow-sm backdrop-blur dark:border-[#202230] dark:bg-[#111423]">
                    <label for="share-code-input" class="text-sm font-semibold text-[#363a4d] dark:text-[#c3c8e6]">
                        {{ __('Código de acesso') }}
                    </label>
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <input id="share-code-input" name="code" type="text" maxlength="8" value="{{ $shareCode }}"
                            placeholder="{{ __('Insira o código de acesso') }}"
                            class="w-full rounded-2xl border border-[#c7cbe0] bg-white/90 px-4 py-3 text-base uppercase tracking-[0.3rem] text-[#101225] placeholder:text-[#9aa0ba] focus:border-[#1f2433] focus:outline-none dark:border-[#2b3045] dark:bg-[#15182a] dark:text-white"
                            required hx-trigger="keyup changed delay:500ms" hx-get="{{ route('compartilhados.index') }}"
                            hx-target="#share-document-list" hx-swap="innerHTML" hx-push-url="true"
                            hx-indicator="#share-loading-indicator" hx-include="#share-search-form" />
                        <div class="flex w-full flex-col gap-3 md:w-auto md:flex-row">
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#1f2433] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#151927] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1f2433] dark:bg-[#6d7bff] dark:hover:bg-[#5560d6]">
                                <span class="material-symbols-rounded text-base" aria-hidden="true">search</span>
                                {{ __('Consultar') }}
                            </button>
                            <button type="button" hx-get="{{ route('compartilhados.index') }}"
                                hx-target="#share-document-list" hx-swap="innerHTML" hx-push-url="true"
                                hx-vals='{"code":""}' hx-indicator="#share-loading-indicator"
                                hx-on::before-request="document.getElementById('share-code-input').value='';"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-transparent bg-transparent px-6 py-3 text-sm font-semibold text-[#5f6473] transition hover:border-[#d0d4e7] hover:bg-[#eef1fb] hover:text-[#1f2433] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d0d4e7] dark:text-[#b5bad3] dark:hover:border-[#31334b] dark:hover:bg-[#191c2c] dark:focus-visible:outline-[#31334b]">
                                <span class="material-symbols-rounded text-base" aria-hidden="true">backspace</span>
                                {{ 'Limpar Lista' }}
                            </button>
                        </div>
                    </div>
                    <div id="share-loading-indicator" class="text-sm text-[#5f6473] dark:text-[#b5bad3]" role="status">
                        {{ 'Carregando documentos...' }}
                    </div>
                </form>

                <div class="mt-10" id="share-document-list">
                    @include('compartilhados.partials.partial-thing')
                </div>
            </section>
        </main>

        {{-- <footer class="mt-auto bg-[#e9ecf8] px-6 py-10 text-[#1f2433] dark:bg-[#0c0f1d] dark:text-[#d8dbef]">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 md:flex-row md:items-center">
                <div class="flex items-center">
                    <div class="h-20 w-20 overflow-hidden rounded-full bg-white p-3 shadow-md dark:bg-[#161a2a]">
                        <img src="{{ asset('assets/images/brand_logo.svg') }}" alt="{{ config('app.name') }}"
                            class="h-full w-full object-contain" />
                    </div>
                </div>
                <div class="flex-1 space-y-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex gap-6 text-base font-medium">
                            <a href="#" class="hover:text-[#6d7bff]">{{ __('Quem Somos') }}</a>
                            <a href="#" class="hover:text-[#6d7bff]">{{ __('Produtos') }}</a>
                        </div>
                        <div class="flex gap-4 text-2xl text-[#1f2433] dark:text-[#d8dbef]">
                            <a href="mailto:tccminhasaude2025@gmail.com"
                                class="inline-flex items-center justify-center rounded-full bg-white p-2 shadow hover:bg-[#eef1fb] dark:bg-[#181c2b] dark:hover:bg-[#1f2335]"
                                aria-label="E-mail">
                                <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                            </a>
                            <a href="https://instagram.com/_avalon.oficial" target="_blank" rel="noreferrer"
                                class="inline-flex items-center justify-center rounded-full bg-white p-2 shadow hover:bg-[#eef1fb] dark:bg-[#181c2b] dark:hover:bg-[#1f2335]"
                                aria-label="Instagram">
                                <span class="material-symbols-rounded" aria-hidden="true">public</span>
                            </a>
                        </div>
                    </div>
                    <div class="h-px w-full bg-[#cfd3ec] dark:bg-[#272b3f]"></div>
                    <div class="flex flex-wrap gap-6 text-sm text-[#5f6473] dark:text-[#b5bad3]">
                        <a href="#" class="hover:text-[#1f2433] dark:hover:text-white">{{ __('Política de Privacidade')
                            }}</a>
                        <a href="#" class="hover:text-[#1f2433] dark:hover:text-white">{{ __('Termos e Condições')
                            }}</a>
                    </div>
                </div>
            </div>
        </footer> --}}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"></script>
</body>

</html>
