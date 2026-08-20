<?php

namespace Tests\Feature\Livewire\Dashboard;

use App\Livewire\Dashboard\Index;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)->assertStatus(200);
    }

    public function test_it_shows_the_correct_stats(): void
    {
        Word::factory()->count(3)->create();
        Word::factory()->due()->count(2)->create();

        Livewire::test(Index::class)
            ->assertSee('5') // total words
            ->assertSee('2'); // reviews due
    }
}
