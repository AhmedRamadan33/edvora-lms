<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 150);
            $table->text('body');
            $table->string('audience', 20);
            $table->json('recipient_ids')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamps();

            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
