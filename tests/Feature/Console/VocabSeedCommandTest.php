<?php

namespace Tests\Feature\Console;

use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocabSeedCommandTest extends TestCase
{
    use RefreshDatabase;

    private function writeFixture(array $entries): string
    {
        $path = storage_path('framework/testing/vocab-seed-test.json');
        file_put_contents($path, json_encode($entries));

        return 'storage/framework/testing/vocab-seed-test.json';
    }

    private function validEntry(string $word): array
    {
        return [
            'word' => $word,
            'pronunciation' => '/test/',
            'part_of_speech' => 'adjective',
            'simple_definition' => 'A simple definition.',
            'urdu_meaning' => 'اردو معنی',
            'detailed_definition' => 'A detailed definition.',
            'examples' => ['An example sentence.'],
            'synonyms' => ['similar'],
            'antonyms' => ['different'],
            'common_collocations' => ['a common phrase'],
            'ielts_relevance' => 'Useful for IELTS.',
            'difficulty_level' => 'B2',
        ];
    }

    public function test_it_imports_valid_entries(): void
    {
        $file = $this->writeFixture([$this->validEntry('meticulous'), $this->validEntry('ubiquitous')]);

        $this->artisan('vocab:seed', ['file' => $file])->assertSuccessful();

        $this->assertDatabaseCount('words', 2);
        $this->assertDatabaseHas('words', ['word' => 'meticulous', 'source' => 'ielts']);
    }

    public function test_it_skips_words_that_already_exist(): void
    {
        Word::factory()->create(['word' => 'meticulous', 'normalized_word' => 'meticulous']);
        $file = $this->writeFixture([$this->validEntry('meticulous'), $this->validEntry('ubiquitous')]);

        $this->artisan('vocab:seed', ['file' => $file])->assertSuccessful();

        // Still just 2 total: the pre-existing one, plus the one new import.
        $this->assertDatabaseCount('words', 2);
    }

    public function test_it_skips_invalid_entries_without_failing_the_whole_run(): void
    {
        $invalid = $this->validEntry('badword');
        unset($invalid['simple_definition']); // required field missing

        $file = $this->writeFixture([$this->validEntry('meticulous'), $invalid]);

        $this->artisan('vocab:seed', ['file' => $file])->assertSuccessful();

        $this->assertDatabaseCount('words', 1);
        $this->assertDatabaseMissing('words', ['word' => 'badword']);
    }

    public function test_it_fails_gracefully_on_a_missing_file(): void
    {
        $this->artisan('vocab:seed', ['file' => 'nonexistent.json'])->assertFailed();
    }

    public function test_running_it_twice_is_idempotent(): void
    {
        $file = $this->writeFixture([$this->validEntry('meticulous')]);

        $this->artisan('vocab:seed', ['file' => $file])->assertSuccessful();
        $this->artisan('vocab:seed', ['file' => $file])->assertSuccessful();

        $this->assertDatabaseCount('words', 1);
    }
}
