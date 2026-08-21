<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['essay', 'speaking', 'vocabulary_practice'])->index();

            // Essay: 'task1'|'task2'. Speaking: 'part1'|'part2'|'part3'. Null for vocab practice.
            $table->string('task_type')->nullable();

            // Essay: the essay question/topic (if the user provided one).
            // Speaking: the AI-generated question.
            // Vocabulary practice: the generated exercise instructions.
            $table->text('prompt_text')->nullable();

            // The user's essay / spoken response (typed) / exercise answer.
            $table->text('submission');

            // Structured AI feedback: always has summary + strengths[] + improvements[],
            // shape otherwise varies by type (see IeltsTutorService for the exact schema
            // per type). Kept as one flexible JSON column rather than one table per type,
            // since all three modes are really the same submit-text/get-feedback shape.
            $table->json('feedback');

            // Only meaningful for essay/speaking.
            $table->decimal('band_overall', 2, 1)->nullable();
            $table->json('band_breakdown')->nullable();

            // Only meaningful for vocabulary_practice — which words this exercise covered.
            $table->json('word_ids')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_sessions');
    }
};
