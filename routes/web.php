<?php

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Progress\Index as ProgressIndex;
use App\Livewire\Review\Index as ReviewIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Vocabulary\AddWord;
use App\Livewire\Vocabulary\Index as VocabularyIndex;
use App\Livewire\Vocabulary\Show as VocabularyShow;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardIndex::class)->name('dashboard');

Route::get('/vocabulary', VocabularyIndex::class)->name('vocabulary.index');
Route::get('/vocabulary/add', AddWord::class)->name('vocabulary.create');
Route::get('/vocabulary/{word}', VocabularyShow::class)->name('vocabulary.show');

Route::get('/review', ReviewIndex::class)->name('review.index');

Route::get('/progress', ProgressIndex::class)->name('progress.index');

Route::get('/settings', SettingsIndex::class)->name('settings');
