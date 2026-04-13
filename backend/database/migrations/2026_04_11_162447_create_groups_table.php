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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_center_id')->constrained('educational_centers')->cascadeOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            $table->string('name'); // Ej: "1º ESO A", "2º DAW"
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
