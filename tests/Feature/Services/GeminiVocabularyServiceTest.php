<?php

namespace Tests\Feature\Services;

use App\Exceptions\GeminiException;
use App\Services\GeminiVocabularyService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiVocabularyServiceTest extends TestCase
{
    private function fakeGeminiResponse(array $overrides = []): array
    {
        return array_merge([
            'word' => 'meticulous',
            'pronunciation' => '/məˈtɪkjələs/',
            'part_of_speech' => 'adjective',
            'simple_definition' => 'Very careful and precise about details.',
            'urdu_meaning' => 'بہت محتاط',
            'detailed_definition' => 'Showing great attention to detail and accuracy.',
            'examples' => ['She is meticulous about her work.'],
            'synonyms' => ['careful', 'precise'],
            'antonyms' => ['careless'],
            'common_collocations' => ['meticulous planning'],
            'ielts_relevance' => 'Useful for describing careful work.',
            'difficulty_level' => 'C1',
        ], $overrides);
    }

    private function mockCandidateResponse(array $data): array
    {
        return [
            'candidates' => [
                ['content' => ['parts' => [['text' => json_encode($data)]]]],
            ],
        ];
    }

    public function test_it_parses_a_valid_gemini_response(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response($this->mockCandidateResponse($this->fakeGeminiResponse())),
        ]);

        $result = app(GeminiVocabularyService::class)->generate('meticulous');

        $this->assertSame('meticulous', $result['word']);
        $this->assertSame('C1', $result['difficulty_level']);
        $this->assertSame(['careful', 'precise'], $result['synonyms']);
    }

    public function test_it_sends_the_context_when_provided(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response($this->mockCandidateResponse($this->fakeGeminiResponse())),
        ]);

        app(GeminiVocabularyService::class)->generate('meticulous', 'He was meticulous about the report.');

        Http::assertSent(function ($request) {
            $prompt = $request->data()['contents'][0]['parts'][0]['text'];

            return str_contains($prompt, 'He was meticulous about the report.');
        });
    }

    public function test_it_throws_on_malformed_json_text(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'not valid json {']]]]],
            ]),
        ]);

        $this->expectException(GeminiException::class);

        app(GeminiVocabularyService::class)->generate('meticulous');
    }

    public function test_it_throws_when_required_fields_are_missing(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response($this->mockCandidateResponse([
                'word' => 'meticulous',
                // missing simple_definition and urdu_meaning
            ])),
        ]);

        $this->expectException(GeminiException::class);

        app(GeminiVocabularyService::class)->generate('meticulous');
    }

    public function test_it_throws_on_an_invalid_difficulty_level(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response($this->mockCandidateResponse(
                $this->fakeGeminiResponse(['difficulty_level' => 'expert-level-9000'])
            )),
        ]);

        $this->expectException(GeminiException::class);

        app(GeminiVocabularyService::class)->generate('meticulous');
    }

    public function test_it_throws_on_a_429_rate_limit_response(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*generateContent*' => Http::response('', 429)]);

        $this->expectException(GeminiException::class);
        $this->expectExceptionMessage('rate limit');

        app(GeminiVocabularyService::class)->generate('meticulous');
    }

    public function test_it_throws_a_safe_message_on_a_server_error(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*generateContent*' => Http::response('Internal error details', 500)]);

        try {
            app(GeminiVocabularyService::class)->generate('meticulous');
            $this->fail('Expected a GeminiException to be thrown.');
        } catch (GeminiException $e) {
            $this->assertStringNotContainsString('Internal error details', $e->getMessage());
        }
    }

    public function test_it_throws_when_no_api_key_is_configured(): void
    {
        config(['gemini.api_key' => null]);

        $this->expectException(GeminiException::class);

        app(GeminiVocabularyService::class)->generate('meticulous');
    }
}
