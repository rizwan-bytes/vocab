<?php

namespace App\Livewire\Vocabulary;

use App\Enums\VocabSource;
use App\Exceptions\GeminiException;
use App\Models\Word;
use App\Services\GeminiVocabularyService;
use App\Services\WordService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Add Word'])]
class AddWord extends Component
{
    #[Validate('required|string|min:1|max:100')]
    public string $word = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $context = null;

    public ?string $source = null;

    /** @var array<string, mixed>|null Gemini's generated card, held for review before saving. */
    public ?array $generatedData = null;

    public ?int $existingWordId = null;

    public ?string $errorMessage = null;

    public function generate(GeminiVocabularyService $gemini, WordService $words): void
    {
        $this->validate();
        $this->errorMessage = null;
        $this->generatedData = null;
        $this->existingWordId = null;

        $existing = $words->findExisting($this->word);

        if ($existing) {
            // Cost control: never call Gemini for a word we already have.
            $this->existingWordId = $existing->id;

            return;
        }

        try {
            $this->generatedData = $gemini->generate($this->word, $this->context ?: null);
        } catch (GeminiException $e) {
            Log::warning('Add Word: Gemini generation failed.', [
                'word' => $this->word,
                'message' => $e->getMessage(),
            ]);
            $this->errorMessage = $e->getMessage();
        }
    }

    public function retry(GeminiVocabularyService $gemini): void
    {
        $this->errorMessage = null;

        try {
            $this->generatedData = $gemini->generate($this->word, $this->context ?: null);
        } catch (GeminiException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function discard(): void
    {
        $this->reset(['word', 'context', 'source', 'generatedData', 'existingWordId', 'errorMessage']);
    }

    public function save(WordService $words): void
    {
        if (! $this->generatedData) {
            return;
        }

        $word = $words->createFromGeminiData(
            $this->generatedData,
            $this->context ?: null,
            $this->source,
        );

        session()->flash('success', "\"{$word->word}\" was added to your vocabulary.");

        $this->redirectRoute('vocabulary.show', $word, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.vocabulary.add-word', [
            'existingWord' => $this->existingWordId ? Word::find($this->existingWordId) : null,
            'sources' => VocabSource::cases(),
        ]);
    }
}
