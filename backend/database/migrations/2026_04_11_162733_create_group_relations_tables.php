<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alumnos en un Grupo
        Schema::create('group_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El estudiante
            $table->timestamps();
        });

        // Profesores que imparten materias en un grupo
        Schema::create('group_tag_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade'); // La materia/módulo
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El profesor
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_tag_teacher');
        Schema::dropIfExists('group_student');
    }
};
