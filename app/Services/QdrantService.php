
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class QdrantService
{
    private static bool $payloadIndexesEnsured = false;
    private static bool $collectionEnsured = false;

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

    /**
     * Ensure Qdrant collection exists.
     */
    private function ensureCollection(): void
    {
        if (self::$collectionEnsured) {
            return;
        }

        $url = $this->baseUrl . '/collections/document_chunks';

        try {
            $response = $this->client()->get($url);

            if ($response->successful()) {
                self::$collectionEnsured = true;
                return;
            }

            if ($response->status() !== 404) {
                $response->throw();
            }

            $this->client()
                ->put($url, [
                    'vectors' => [
                        'size' => 384,
                        'distance' => 'Cosine',
                    ],
                ])
                ->throw();

            self::$collectionEnsured = true;

            Log::info('Qdrant collection created successfully.', [
                'collection' => 'document_chunks',
            ]);

        } catch (\Throwable $exception) {

            Log::error('Qdrant collection initialization failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Ensure payload indexes exist.
     */
    public function ensurePayloadIndexes(): void
    {
        if (self::$payloadIndexesEnsured) {
            return;
        }

        $this->ensureCollection();

        $indexes = ['document_id', 'user_id', 'chunk_id'];

        foreach ($indexes as $field) {
            try {
                $this->client()
                    ->put(
                        $this->baseUrl . '/collections/document_chunks/index',
                        [
                            'field_name' => $field,
                            'field_schema' => 'integer',
                        ]
                    )
                    ->throw();

            } catch (\Throwable $exception) {

                $message = $exception->getMessage();

                if (
                    ! str_contains($message, 'already exists') &&
                    ! str_contains($message, 'exists')
                ) {
                    Log::warning(
                        'Qdrant payload index creation skipped or failed.',
                        [
                            'field' => $field,
                            'error' => $message,
                        ]
                    );
                }
            }
        }

        self::$payloadIndexesEnsured = true;
    }

    /**
     * Store vector in Qdrant.
     */
    public function store(
        int $chunkId,
        array $vector,
        array $payload
    ): void {
        try {
            $this->ensurePayloadIndexes();

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

    /**
     * Search vectors in Qdrant.
     */
    public function search(
        array $vector,
        int $userId,
        int $limit = 10,
        float $scoreThreshold = 0.0
    ): array {
        try {
            $this->ensurePayloadIndexes();

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

    /**
     * Delete all vectors belonging to a document.
     */
    public function deleteByDocument(int $documentId): void
    {
        try {
            $this->ensureCollection();

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

            Log::info('Qdrant document vectors deleted.', [
                'document_id' => $documentId,
            ]);

        } catch (\Throwable $exception) {

            Log::error('Qdrant document deletion failed.', [
                'document_id' => $documentId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
