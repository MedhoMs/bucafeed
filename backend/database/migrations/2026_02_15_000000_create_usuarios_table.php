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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');

            $table->string('role'); // user, institution, student, teacher, admin, moderator

            $table->integer('reputation')->default(0);
            $table->foreignId('educational_center_id')->nullable()
                ->constrained('educational_centers');

            $table->string('national_id')->unique(); // dni

            $table->enum('education_level', ['primary','secondary'])->nullable();
            $table->string('institution_name')->nullable();

            $table->timestamps();
        });
    }



    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

