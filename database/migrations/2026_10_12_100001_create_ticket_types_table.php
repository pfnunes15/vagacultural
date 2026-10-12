<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_occurrence_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 8, 2)->default(0)->comment('0 = free (Phase 1)');
            $table->unsignedInteger('capacity')->comment('Total seats for this type');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('sales_start_at')->nullable();
            $table->timestamp('sales_end_at')->nullable();
            $table->timestamps();

            $table->index(['event_occurrence_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
