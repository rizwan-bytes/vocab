<?php

namespace Tests\Feature\Livewire\Progress;

use App\Livewire\Progress\Index;
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
}
