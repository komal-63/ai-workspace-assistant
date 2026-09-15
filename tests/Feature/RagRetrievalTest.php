<?php

namespace Tests\Feature;

use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use App\Services\RAGService;
use Mockery;
use Tests\TestCase;

class RagRetrievalTest extends TestCase
{
    public function test_obvious_document_question_routes_without_classifier_call(): void
    {
        $ai = Mockery::mock(AIService::class);
        $ai->shouldReceive('generateResponse')->never();

        $service = new RAGService(
            Mockery::mock(EmbeddingService::class),
            Mockery::mock(QdrantService::class),
            $ai
        );

        $this->assertTrue($service->isDocumentQuestion("What is my father's name?"));
    }

    public function test_follow_up_question_rewrite_uses_history(): void
    {
        $ai = Mockery::mock(AIService::class);
        $ai->shouldReceive('generateResponse')
            ->once()
            ->withArgs(function (array $messages) {
                $content = $messages[0]['content'] ?? '';
                return str_contains($content, "What about his role?")
                    && str_contains($content, "What is the employee's designation?");
            })
            ->andReturn("What is the employee's role?");

        $service = new RAGService(
            Mockery::mock(EmbeddingService::class),
            Mockery::mock(QdrantService::class),
            $ai
        );

        $this->assertSame(
            "What is the employee's role?",
            $service->rewriteQuestionForRetrieval(
                "What about his role?",
                [
                    ['role' => 'user', 'content' => "What is the employee's designation?"],
                    ['role' => 'assistant', 'content' => 'Admin Executive'],
                ]
            )
        );
    }

    public function test_hybrid_retrieval_merges_and_deduplicates_candidates(): void
    {
        $service = new RAGService(
            Mockery::mock(EmbeddingService::class),
            Mockery::mock(QdrantService::class),
            Mockery::mock(AIService::class)
        );

        $semantic = [
            ['score' => 0.94, 'payload' => ['document_id' => 42, 'chunk_id' => 7, 'content' => 'Father\'s name is Sunny Kumar']],
            ['score' => 0.82, 'payload' => ['document_id' => 42, 'chunk_id' => 7, 'content' => 'Father\'s name is Sunny Kumar']],
            ['score' => 0.69, 'payload' => ['document_id' => 99, 'chunk_id' => 12, 'content' => 'Employee designation is Manager']],
        ];

        $lexical = [
            ['score' => 0.91, 'payload' => ['document_id' => 42, 'chunk_id' => 7, 'content' => 'Father\'s name is Sunny Kumar']],
            ['score' => 0.71, 'payload' => ['document_id' => 77, 'chunk_id' => 15, 'content' => 'Roll number is 1018640148']],
        ];

        $merged = $service->mergeCandidates($semantic, $lexical);

        $this->assertCount(3, $merged);
        $this->assertSame(42, $merged[0]['payload']['document_id']);
        $this->assertSame(7, $merged[0]['payload']['chunk_id']);
    }

    public function test_source_document_is_validated_against_retrieved_candidates(): void
    {
        $service = new RAGService(
            Mockery::mock(EmbeddingService::class),
            Mockery::mock(QdrantService::class),
            Mockery::mock(AIService::class)
        );

        $context = [
            ['score' => 0.95, 'payload' => ['document_id' => 42, 'chunk_id' => 7, 'content' => 'Father\'s name is Sunny Kumar']],
            ['score' => 0.81, 'payload' => ['document_id' => 99, 'chunk_id' => 10, 'content' => 'Employee designation is Manager']],
        ];

        $result = [
            'status' => 'FULL',
            'answer' => 'Sunny Kumar',
            'supporting_document_id' => 999,
        ];

        $this->assertSame(42, $service->resolveSupportingDocumentId($context, $result));
    }
}
