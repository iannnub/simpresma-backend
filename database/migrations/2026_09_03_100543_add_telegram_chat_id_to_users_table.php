<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('no_whatsapp')
                ->comment('Telegram Chat ID untuk notifikasi bot @simpresma_unej_bot');
            $table->timestamp('telegram_connected_at')->nullable()->after('telegram_chat_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_connected_at']);
        });
    }
};
