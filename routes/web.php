<?php

use Contensio\Polls\Http\Controllers\Admin\PollController;
use Contensio\Polls\Http\Controllers\Frontend\VoteController;
use Illuminate\Support\Facades\Route;

// ── Admin routes ─────────────────────────────────────────────────────────────

Route::prefix(config('contensio.route_prefix', 'account'))
    ->middleware(['web', 'contensio.auth', 'contensio.admin'])
    ->group(function () {
        Route::get('/polls',              [PollController::class, 'index'])  ->name('contensio-polls.index');
        Route::get('/polls/create',       [PollController::class, 'create']) ->name('contensio-polls.create');
        Route::post('/polls',             [PollController::class, 'store'])  ->name('contensio-polls.store');
        Route::get('/polls/{id}/edit',    [PollController::class, 'edit'])   ->name('contensio-polls.edit');
        Route::put('/polls/{id}',         [PollController::class, 'update']) ->name('contensio-polls.update');
        Route::delete('/polls/{id}',      [PollController::class, 'destroy'])->name('contensio-polls.destroy');
        Route::get('/polls/{id}/results', [PollController::class, 'results'])->name('contensio-polls.results');
    });

// ── Public JSON API ───────────────────────────────────────────────────────────

Route::middleware('web')->group(function () {
    Route::post('/polls/{id}/vote',    [VoteController::class, 'vote'])   ->name('contensio-polls.vote');
    Route::get('/polls/{id}/results',  [VoteController::class, 'results'])->name('contensio-polls.results.json');
});
