<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_occurrence_id')->nullable()->constrained('event_occurrences')->nullOnDelete();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_id', 'event_occurrence_id'], 'agenda_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_items');
    }
};
