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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Activity type (e.g., 'video.created', 'playlist.updated', 'stream.started')
            $table->string('activity_type');

            // Polymorphic relation to the subject (Video, Playlist, StreamSession)
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');

            // Serialized metadata (JSON)
            $table->json('metadata')->nullable();

            $table->timestamp('created_at');

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
