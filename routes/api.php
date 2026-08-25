<?php
use Illuminate\Support\Facades\Route;

Route::get('/test-ai-exception', function () {
    throw new \App\Exceptions\AIServiceException(
        'Testing AI service exception.'
    );
});