<?php

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Settings'])]
class Index extends Component
{
    public function render(): View
    {
        $apiKey = config('gemini.api_key');

        return view('livewire.settings.index', [
            'geminiConfigured' => filled($apiKey),
            'geminiKeyPreview' => $apiKey ? substr($apiKey, 0, 6).'••••••••'.substr($apiKey, -4) : null,
            'geminiModel' => config('gemini.model'),
        ]);
    }
}
