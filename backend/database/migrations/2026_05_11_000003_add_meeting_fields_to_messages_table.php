<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_id')->nullable()->after('chat_id');
            $table->string('message_type', 50)->default('text')->after('content');
            $table->string('file_name')->nullable()->after('message_type');
            $table->json('metadata')->nullable()->after('file_name');

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->dropColumn(['meeting_id', 'message_type', 'file_name', 'metadata']);
        });
    }
};
