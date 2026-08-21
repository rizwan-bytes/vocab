<?php

namespace Tests\Feature\Livewire\Tutor;

use App\Livewire\Tutor\Index;
use App\Models\TutorSession;
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

    public function test_it_shows_the_empty_state_with_no_sessions(): void
    {
        Livewire::test(Index::class)->assertSee('No tutor sessions yet');
    }

    public function test_it_lists_recent_sessions_with_band_scores(): void
    {
        TutorSession::factory()->create(['band_overall' => 6.5]);

        Livewire::test(Index::class)->assertSee('Band 6.5');
    }
}
