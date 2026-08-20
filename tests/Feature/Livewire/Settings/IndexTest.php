<?php

namespace Tests\Feature\Livewire\Settings;

use App\Livewire\Settings\Index;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)->assertStatus(200);
    }

    public function test_it_shows_configured_when_the_api_key_is_set(): void
    {
        config(['gemini.api_key' => 'AQ.test-key']);

        Livewire::test(Index::class)->assertSee('Connected');
    }

    public function test_it_shows_not_configured_when_the_api_key_is_missing(): void
    {
        config(['gemini.api_key' => null]);

        Livewire::test(Index::class)->assertSee('Not configured');
    }
}
