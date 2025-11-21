<?php

use App\Http\Controllers\ViewShareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('compartilhados.index'));
});

Route::get('/compartilhados', [ViewShareController::class, 'index'])
    ->name('compartilhados.index');

Route::get('/compartilhados/{document}', [ViewShareController::class, 'download'])
    ->whereUuid('document')
    ->name('compartilhados.download');
