<?php

use App\Http\Controllers\AliasGameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AliasGameController::class, 'index'])->name('alias.index');
Route::post('/alias/start', [AliasGameController::class, 'start'])->name('alias.start');
Route::post('/alias/turn/start', [AliasGameController::class, 'startTurn'])->name('alias.turn.start');
Route::post('/alias/word/mark', [AliasGameController::class, 'mark'])->name('alias.word.mark');
Route::post('/alias/turn/finish', [AliasGameController::class, 'finishTurn'])->name('alias.turn.finish');
Route::post('/alias/turn/review', [AliasGameController::class, 'review'])->name('alias.turn.review');
Route::post('/alias/next', [AliasGameController::class, 'next'])->name('alias.next');
Route::post('/alias/quit', [AliasGameController::class, 'quit'])->name('alias.quit');
