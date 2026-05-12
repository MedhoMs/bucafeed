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
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('area')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('educational_center_cycle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_center_id')->constrained('educational_centers')->onDelete('cascade');
            $table->foreignId('cycle_id')->constrained('cycles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educational_center_cycle');
        Schema::dropIfExists('cycles');
    }
};
