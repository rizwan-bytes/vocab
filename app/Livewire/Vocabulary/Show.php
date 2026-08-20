<?php

namespace App\Livewire\Vocabulary;

use App\Models\Word;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Word $word;

    public bool $editing = false;

    #[Validate('required|string|max:1000')]
    public string $simple_definition = '';

    #[Validate('nullable|string|max:2000')]
    public ?string $detailed_definition = null;

    #[Validate('nullable|string|max:1000')]
    public ?string $urdu_meaning = null;

    #[Validate('nullable|string|max:1000')]
    public ?string $context = null;

    public function mount(Word $word): void
    {
        $this->word = $word;
    }

    public function startEditing(): void
    {
        $this->simple_definition = $this->word->simple_definition;
        $this->detailed_definition = $this->word->detailed_definition;
        $this->urdu_meaning = $this->word->urdu_meaning;
        $this->context = $this->word->context;
        $this->editing = true;
    }

    public function save(): void
    {
        $this->validate();

        $this->word->update([
            'simple_definition' => $this->simple_definition,
            'detailed_definition' => $this->detailed_definition,
            'urdu_meaning' => $this->urdu_meaning,
            'context' => $this->context,
        ]);

        $this->editing = false;
        session()->flash('success', 'Word updated.');
    }

    public function delete(): void
    {
        $wordName = $this->word->word;
        $this->word->delete();

        session()->flash('success', "\"{$wordName}\" was deleted.");
        $this->redirectRoute('vocabulary.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.vocabulary.show');
    }
}
