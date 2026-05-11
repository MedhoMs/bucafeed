<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kahoot_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('kahoot_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('question_index');
            $table->integer('selected_answer');
            $table->boolean('is_correct');
            $table->integer('score')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'user_id', 'question_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kahoot_answers');
    }
};
