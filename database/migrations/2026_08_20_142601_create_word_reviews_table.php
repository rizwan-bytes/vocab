<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->cascadeOnDelete();

            $table->date('review_date');
            $table->enum('rating', ['again', 'hard', 'good', 'easy']);

            $table->timestamp('next_review_at')->nullable();

            // Cumulative correct/wrong counts for the parent word, snapshotted at
            // the moment of this review (not per-review counts) — lets progress be
            // read directly off the review history without recomputing on every
            // dashboard/report query.
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);

            $table->timestamps();

            $table->index(['word_id', 'review_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_reviews');
    }
};
