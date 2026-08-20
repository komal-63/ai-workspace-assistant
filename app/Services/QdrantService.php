<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class QdrantService
{
    private string $baseUrl = 'http://127.0.0.1:6333';

    public function store(
        int $chunkId,
        array $vector,
        array $payload
    ): void {
        Http::put(
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
        )->throw();
    }

    public function search(
            array $vector,
            int $userId,
            int $limit = 5,
            float $scoreThreshold = 0.20
        ): array {
            $response = Http::post(
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

            $result = $response->json('result', []);

           
            return $result;
        }
}