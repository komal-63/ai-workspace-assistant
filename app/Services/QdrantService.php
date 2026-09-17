<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class QdrantService
{
    private static bool $payloadIndexesEnsured = false;

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

    public function ensurePayloadIndexes(): void
    {
        if (self::$payloadIndexesEnsured) {
            return;
        }

        $indexes = ['document_id', 'user_id', 'chunk_id'];

        foreach ($indexes as $field) {
            try {
                $this->client()
                    ->post(
                        $this->baseUrl . '/collections/document_chunks/index',
                        [
                            'field_name' => $field,
                            'field_schema' => 'integer',
                        ]
                    )
                    ->throw();
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();
                if (! str_contains($message, 'already exists') && ! str_contains($message, 'exists')) {
                    Log::warning('Qdrant payload index creation skipped or failed.', [
                        'field' => $field,
                        'error' => $message,
                    ]);
                }
            }
        }

        self::$payloadIndexesEnsured = true;
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
    int $limit = 10,
    float $scoreThreshold = 0.0
): array {
    try {

        $response = $this->client()
            ->post(
                $this->baseUrl . '/collections/document_chunks/points/search',
                [
                    'vector' => $vector,

                    // Temporary debugging:
                    // retrieve more chunks so we can inspect ranking.
                    'limit' => $limit,

                    'with_payload' => true,

                    // Temporary debugging:
                    // don't remove low-scoring chunks yet.
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

        $results = $response->json('result', []);

        Log::info('Qdrant search results', [
            'user_id' => $userId,
            'requested_limit' => $limit,
            'score_threshold' => $scoreThreshold,
            'result_count' => count($results),
        ]);

        return $results;

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