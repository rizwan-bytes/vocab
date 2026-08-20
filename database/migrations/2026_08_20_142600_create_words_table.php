<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('word');
            $table->string('normalized_word')->index();

            $table->string('pronunciation')->nullable();
            $table->string('part_of_speech')->nullable();

            $table->text('simple_definition')->nullable();
            $table->text('detailed_definition')->nullable();
            $table->text('urdu_meaning')->nullable();

            $table->json('examples')->nullable();
            $table->json('synonyms')->nullable();
            $table->json('antonyms')->nullable();
            $table->json('common_collocations')->nullable();

            $table->text('ielts_relevance')->nullable();
            $table->string('difficulty_level')->nullable();

            $table->text('context')->nullable();
            $table->string('source')->nullable();

            $table->enum('status', ['new', 'learning', 'reviewing', 'mastered'])
                ->default('new')
                ->index();

            // Denormalized from the latest word_reviews row (or set to now()+1 day
            // at creation, before any review exists) so "words due today" is a plain
            // indexed WHERE clause instead of a correlated subquery per word.
            $table->timestamp('next_review_at')->nullable()->index();
            $table->timestamp('last_reviewed_at')->nullable();

            $table->timestamps();

            // No DB-level unique constraint on normalized_word: user_id is nullable
            // (no auth in the MVP yet), and MySQL treats multiple NULLs as distinct in
            // a unique index, so it wouldn't actually prevent duplicates while user_id
            // is null. Duplicate prevention is enforced in the application layer
            // instead (see WordService::findExisting()); the index above keeps that
            // lookup fast.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
