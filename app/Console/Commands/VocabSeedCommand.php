<?php

namespace App\Console\Commands;

use App\Services\GeminiVocabularyService;
use App\Services\WordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Imports pre-generated vocabulary cards from a JSON file — used for the
 * hand-curated 500-word IELTS seed list, which was written directly rather
 * than generated through GeminiVocabularyService (this sandbox's network
 * environment blocks the live Gemini generateContent call, per CLAUDE.md).
 * Each entry is still validated against the exact same rules a real Gemini
 * response would go through, and duplicate words are skipped — so this
 * command is safe to re-run if it's interrupted partway.
 */
class VocabSeedCommand extends Command
{
    protected $signature = 'vocab:seed {file=database/seeders/data/ielts-500-words.json}';

    protected $description = 'Import a JSON file of pre-built vocabulary cards into the words table';

    public function handle(WordService $words): int
    {
        $path = base_path($this->argument('file'));

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $entries = json_decode(file_get_contents($path), true);

        if (! is_array($entries)) {
            $this->error('File does not contain a valid JSON array.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar(count($entries));
        $bar->start();

        $created = 0;
        $skipped = 0;
        $invalid = 0;

        foreach ($entries as $entry) {
            $validator = Validator::make($entry, GeminiVocabularyService::validationRules());

            if ($validator->fails()) {
                $invalid++;
                $this->newLine();
                $this->warn('Skipped invalid entry "'.($entry['word'] ?? '?').'": '.$validator->errors()->first());
                $bar->advance();

                continue;
            }

            $data = $validator->validated();

            if ($words->findExisting($data['word'])) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $words->createFromGeminiData($data, null, 'ielts');
            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Created: {$created}  |  Skipped (already exists): {$skipped}  |  Invalid: {$invalid}");

        return self::SUCCESS;
    }
}
