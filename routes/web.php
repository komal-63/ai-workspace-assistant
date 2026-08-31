<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\DocumentController;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use App\Services\AIService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])
        ->name('admin.users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])
        ->name('admin.users.show');
    Route::put('/users/{user}/role', [AdminController::class, 'updateRole'])
    ->name('admin.users.update-role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])
    ->name('admin.users.delete');
});


Route::middleware('auth')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])
        ->name('conversations.index');
    Route::post('/conversations', [ConversationController::class, 'store'])
        ->name('conversations.store');
});


Route::get('/conversations/{conversation}', [MessageController::class, 'index'])
    ->name('messages.index');
Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])
    ->name('messages.store');

Route::middleware('auth')->group(function () {

    Route::get('/documents', [DocumentController::class, 'index'])
        ->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])
        ->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
    ->name('documents.destroy');
    Route::get('/documents/{document}/view', [DocumentController::class, 'view'])
    ->name('documents.view');

});

require __DIR__.'/auth.php';
