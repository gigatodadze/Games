<?php

use App\Http\Controllers\AliasGameController;
use App\Http\Controllers\GameHubController;
use App\Http\Controllers\NamiokobanaGameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameHubController::class, 'index'])->name('games.index');
Route::post('/alias/start', [AliasGameController::class, 'start'])->name('alias.start');
Route::post('/alias/turn/start', [AliasGameController::class, 'startTurn'])->name('alias.turn.start');
Route::post('/alias/word/mark', [AliasGameController::class, 'mark'])->name('alias.word.mark');
Route::post('/alias/turn/finish', [AliasGameController::class, 'finishTurn'])->name('alias.turn.finish');
Route::post('/alias/turn/review', [AliasGameController::class, 'review'])->name('alias.turn.review');
Route::post('/alias/next', [AliasGameController::class, 'next'])->name('alias.next');
Route::post('/alias/quit', [AliasGameController::class, 'quit'])->name('alias.quit');

Route::post('/namiokobana/start', [NamiokobanaGameController::class, 'start'])->name('namiokobana.start');
Route::post('/namiokobana/word', [NamiokobanaGameController::class, 'word'])->name('namiokobana.word');
Route::post('/namiokobana/reveal', [NamiokobanaGameController::class, 'reveal'])->name('namiokobana.reveal');
Route::post('/namiokobana/begin', [NamiokobanaGameController::class, 'begin'])->name('namiokobana.begin');
Route::post('/namiokobana/finish', [NamiokobanaGameController::class, 'finish'])->name('namiokobana.finish');
Route::post('/namiokobana/next', [NamiokobanaGameController::class, 'next'])->name('namiokobana.next');
Route::post('/namiokobana/quit', [NamiokobanaGameController::class, 'quit'])->name('namiokobana.quit');
