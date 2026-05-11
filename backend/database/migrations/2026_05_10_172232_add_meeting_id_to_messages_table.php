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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('meeting_id')
                  ->nullable()
                  ->after('group_id')
                  ->constrained('meetings')
                  ->cascadeOnDelete();

            $table->index(['meeting_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->dropIndex(['meeting_id', 'created_at']);
            $table->dropColumn('meeting_id');
        });
    }
};
