<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');

            $table->unsignedInteger('new_words')->default(0);
            $table->unsignedInteger('reviewed_words')->default(0);
            $table->unsignedInteger('mastered_words')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('wrong_answers')->default(0);
            $table->unsignedInteger('study_minutes')->default(0);

            $table->timestamps();

            // Nullable user_id means this can't be a true unique constraint yet
            // (same NULL-distinctness caveat as words.normalized_word) — enforced
            // as "one row per day" in the application layer via DailyProgressService,
            // this index just keeps the date lookup fast.
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
