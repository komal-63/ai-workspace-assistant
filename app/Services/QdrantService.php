<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class QdrantService
{
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.qdrant.url'),
            '/'
        );

        $this->apiKey = config('services.qdrant.api_key');
    }

    private function client()
    {
        return Http::timeout(30)
            ->withHeaders([
                'api-key' => $this->apiKey,
            ]);
    }

    public function store(
        int $chunkId,
        array $vector,
        array $payload
    ): void {
        try {
            $this->client()
                ->put(
                    $this->baseUrl . '/collections/document_chunks/points',
                    [
                        'points' => [
                            [
                                'id' => $chunkId,
                                'vector' => $vector,
                                'payload' => $payload,
                            ],
                        ],
                    ]
                )
                ->throw();

        } catch (\Throwable $exception) {

            Log::error('Qdrant store failed.', [
                'chunk_id' => $chunkId,
                'document_id' => $payload['document_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function search(
        array $vector,
        int $userId,
        int $limit = 5,
        float $scoreThreshold = 0.20
    ): array {
        try {
            $response = $this->client()
                ->post(
                    $this->baseUrl . '/collections/document_chunks/points/search',
                    [
                        'vector' => $vector,
                        'limit' => $limit,
                        'with_payload' => true,
                        'score_threshold' => $scoreThreshold,

                        'filter' => [
                            'must' => [
                                [
                                    'key' => 'user_id',
                                    'match' => [
                                        'value' => $userId,
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

            $response->throw();

            return $response->json('result', []);

        } catch (\Throwable $exception) {

            Log::error('Qdrant search failed.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function deleteByDocument(int $documentId): void
    {
        try {
            $this->client()
                ->post(
                    $this->baseUrl . '/collections/document_chunks/points/delete',
                    [
                        'filter' => [
                            'must' => [
                                [
                                    'key' => 'document_id',
                                    'match' => [
                                        'value' => $documentId,
                                    ],
                                ],
                            ],
                        ],
                    ]
                )
                ->throw();

        } catch (\Throwable $exception) {

            Log::error('Qdrant document deletion failed.', [
                'document_id' => $documentId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}