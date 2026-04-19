<?php

use Contensio\Polls\Http\Controllers\Admin\PollController;
use Contensio\Polls\Http\Controllers\Frontend\VoteController;
use Illuminate\Support\Facades\Route;

// ── Admin routes ─────────────────────────────────────────────────────────────

Route::prefix(config('contensio.route_prefix', 'account'))
    ->middleware(['web', 'contensio.auth', 'contensio.admin'])
    ->group(function () {
        Route::get('/polls',              [PollController::class, 'index'])  ->name('polls.index');
        Route::get('/polls/create',       [PollController::class, 'create']) ->name('polls.create');
        Route::post('/polls',             [PollController::class, 'store'])  ->name('polls.store');
        Route::get('/polls/{id}/edit',    [PollController::class, 'edit'])   ->name('polls.edit');
        Route::put('/polls/{id}',         [PollController::class, 'update']) ->name('polls.update');
        Route::delete('/polls/{id}',      [PollController::class, 'destroy'])->name('polls.destroy');
        Route::get('/polls/{id}/results', [PollController::class, 'results'])->name('polls.results');
    });

// ── Public JSON API ───────────────────────────────────────────────────────────

Route::middleware('web')->group(function () {
    Route::post('/polls/{id}/vote',    [VoteController::class, 'vote'])   ->name('polls.vote');
    Route::get('/polls/{id}/results',  [VoteController::class, 'results'])->name('polls.results.json');
});
