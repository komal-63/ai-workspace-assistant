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

});

Route::get('/test-embedding', function (EmbeddingService $embeddingService) {
    $vector = $embeddingService->generate(
        'Laravel Sanctum is used for API authentication.'
    );

    return [
        'dimensions' => count($vector),
        'first_five_values' => array_slice($vector, 0, 5),
    ];
});

Route::get('/test-qdrant', function (
    EmbeddingService $embeddingService,
    QdrantService $qdrantService
) {
    $text = 'Laravel Sanctum is used for API authentication.';

    $vector = $embeddingService->generate($text);

    $qdrantService->store(
        chunkId: 999,
        vector: $vector,
        payload: [
            'document_id' => 1,
            'content' => $text,
        ]
    );

    return [
        'message' => 'Vector stored successfully',
        'dimensions' => count($vector),
    ];
});

Route::get('/test-search', function (
    EmbeddingService $embeddingService,
    QdrantService $qdrantService
) {
    $question = 'How does Laravel authentication work?';

    $vector = $embeddingService->generate($question);

    $results = $qdrantService->search($vector, 3);

    return [
        'question' => $question,
        'results' => $results,
    ];
});


Route::get('/test-rag', function (
    EmbeddingService $embeddingService,
    QdrantService $qdrantService,
    AIService $aiService
) {
    $question = 'How does Laravel authentication work?';

    // 1. Question → embedding
    $vector = $embeddingService->generate($question);

    // 2. Embedding → relevant chunks
    $results = $qdrantService->search($vector, 3);

    // 3. Relevant chunks → Groq → answer
    $answer = $aiService->generateAnswer($question, $results);

    return [
        'question' => $question,
        'retrieved_chunks' => $results,
        'answer' => $answer,
    ];
});

require __DIR__.'/auth.php';
