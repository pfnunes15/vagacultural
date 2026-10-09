<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_occurrences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->boolean('is_all_day')->default(false)->comment('Continuous/all-day run between starts_at and ends_at');
            $table->boolean('is_online')->default(false);
            $table->string('online_url')->nullable()->comment('Link when the occurrence is online');
            $table->string('address')->nullable()->comment('One-off physical address when no venue is selected');
            $table->string('postal_code', 16)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('door_time')->nullable();
            $table->string('status', 16)->default('scheduled');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'status']);
            $table->index(['event_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_occurrences');
    }
};
