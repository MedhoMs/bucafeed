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
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');

            $table->string('role'); // user, institution, student, teacher, admin, moderator

            $table->integer('reputation')->default(0);
            $table->foreignId('educational_center_id')->nullable()
                ->constrained('educational_centers');

            $table->string('dni')->nullable()->unique(); // dni

            $table->string('education_level')->nullable();
            $table->string('institution_name')->nullable();

            // Customized Profile Fields
            $table->string('profile_picture')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();

            // Stats / Social fields
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
            $table->unsignedInteger('publications_count')->default(0);
            $table->unsignedInteger('questions_uploaded_count')->default(0);
            $table->unsignedInteger('events_created_count')->default(0);
            $table->unsignedInteger('questions_answered_count')->default(0);
            $table->unsignedInteger('events_participated_count')->default(0);
            
            // Reputation level
            $table->unsignedInteger('reputation_level')->default(1);

            // Additional missing fields useful for social customization
            $table->string('website_url')->nullable();
            $table->string('location')->nullable();
            $table->json('social_links')->nullable();

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






