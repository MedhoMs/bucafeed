<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'message_type')) {
                $table->string('message_type', 50)->default('text')->after('content');
            }
            if (!Schema::hasColumn('messages', 'file_name')) {
                $table->string('file_name')->nullable()->after('message_type');
            }
            if (!Schema::hasColumn('messages', 'metadata')) {
                $table->json('metadata')->nullable()->after('file_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('messages', 'message_type')) $columns[] = 'message_type';
            if (Schema::hasColumn('messages', 'file_name')) $columns[] = 'file_name';
            if (Schema::hasColumn('messages', 'metadata')) $columns[] = 'metadata';
            if (!empty($columns)) $table->dropColumn($columns);
        });
    }
};
