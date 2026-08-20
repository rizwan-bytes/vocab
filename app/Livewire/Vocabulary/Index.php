<?php

namespace App\Livewire\Vocabulary;

use App\Enums\WordStatus;
use App\Models\Word;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Vocabulary'])]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $difficulty = '';

    #[Url]
    public string $ielts = '';

    #[Url]
    public string $sort = 'newest';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'difficulty', 'ielts', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'difficulty', 'ielts']);
        $this->resetPage();
    }

    public function delete(Word $word): void
    {
        $wordName = $word->word;
        $word->delete();
        session()->flash('success', "\"{$wordName}\" was deleted.");
    }

    public function render(): View
    {
        $words = Word::query()
            ->search($this->search)
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->difficulty, fn ($q) => $q->where('difficulty_level', $this->difficulty))
            ->when($this->ielts, fn ($q) => $q->whereNotNull('ielts_relevance'))
            ->orderBy('created_at', $this->sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.vocabulary.index', [
            'words' => $words,
            'statuses' => WordStatus::cases(),
            'difficulties' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
        ]);
    }
}
