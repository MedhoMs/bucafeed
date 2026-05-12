<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kahoot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->json('questions');
            $table->enum('status', ['pending', 'active', 'finished'])->default('pending');
            $table->integer('current_question_index')->default(0);
            $table->integer('time_per_question')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kahoot_sessions');
    }
};
