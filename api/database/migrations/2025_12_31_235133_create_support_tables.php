<?php

use App\Api\V1\Enum\SupportStatusEnum;
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
        Schema::create('support_tickets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->string('subject');
            $blueprint->enum('status', SupportStatusEnum::values())->default(SupportStatusEnum::NEW->value);
            $blueprint->timestamp('read_at')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->text('message')->nullable();
            $blueprint->string('file_path')->nullable();
            $blueprint->string('file_type')->nullable(); // image, video, etc.
            $blueprint->string('file_name')->nullable();
            $blueprint->unsignedBigInteger('file_size')->nullable();
            $blueprint->boolean('is_admin')->default(false);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
