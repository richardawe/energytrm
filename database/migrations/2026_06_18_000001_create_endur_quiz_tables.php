<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('endur_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->string('module_id');
            $table->unsignedTinyInteger('sort_order');
            $table->text('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->char('correct_option', 1); // a, b, c, or d
            $table->timestamps();
            $table->index(['module_id', 'sort_order']);
        });

        Schema::create('endur_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module_id');
            $table->unsignedTinyInteger('score');       // 0–10
            $table->boolean('passed');                  // score >= 9
            $table->json('answers')->nullable();        // {question_id: chosen_option}
            $table->timestamp('passed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endur_quiz_attempts');
        Schema::dropIfExists('endur_quiz_questions');
    }
};
