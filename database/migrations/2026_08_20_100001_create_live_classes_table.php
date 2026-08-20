<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('provider_meeting_id')->nullable();
            $table->text('join_url')->nullable();
            $table->text('start_url')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'scheduled_at']);
            $table->index('instructor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_classes');
    }
};
