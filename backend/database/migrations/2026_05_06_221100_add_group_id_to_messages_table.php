<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('chat_id')->nullable()->change();

            $table->foreignId('group_id')
                  ->nullable()
                  ->after('chat_id')
                  ->constrained('groups')
                  ->cascadeOnDelete();

            $table->index(['group_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id', 'created_at']);
            $table->dropColumn('group_id');
        });
    }
};
