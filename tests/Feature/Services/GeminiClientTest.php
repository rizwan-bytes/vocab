<?php

namespace Tests\Feature\Services;

use App\Exceptions\GeminiException;
use App\Services\GeminiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiClientTest extends TestCase
{
    public function test_it_returns_the_decoded_json_from_the_candidate_text(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"foo":"bar"}']]]]],
            ]),
        ]);

        $result = app(GeminiClient::class)->generate('a prompt');

        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function test_it_includes_the_response_schema_when_given_one(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{}']]]]],
            ]),
        ]);

        $schema = ['type' => 'OBJECT', 'properties' => ['foo' => ['type' => 'STRING']]];
        app(GeminiClient::class)->generate('a prompt', $schema);

        Http::assertSent(function ($request) use ($schema) {
            $config = $request->data()['generationConfig'];

            return $config['responseSchema'] === $schema
                && $config['responseMimeType'] === 'application/json';
        });
    }

    public function test_it_omits_response_schema_config_when_none_given(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{}']]]]],
            ]),
        ]);

        app(GeminiClient::class)->generate('a prompt');

        Http::assertSent(fn ($request) => ! array_key_exists('responseSchema', $request->data()['generationConfig'] ?? []));
    }

    public function test_it_throws_on_missing_api_key(): void
    {
        config(['gemini.api_key' => null]);

        $this->expectException(GeminiException::class);

        app(GeminiClient::class)->generate('a prompt');
    }

    public function test_it_throws_on_malformed_json(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'not json']]]]],
            ]),
        ]);

        $this->expectException(GeminiException::class);

        app(GeminiClient::class)->generate('a prompt');
    }
}
