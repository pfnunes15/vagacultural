<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pricing tiers for an event (e.g. age-based prices). There is no ticketing
 * engine yet — these describe prices only; a ticketing system comes later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_ticket_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120)->comment('e.g. Adulto, Criança, Sénior');
            $table->decimal('price', 8, 2)->nullable()->comment('null = free tier');
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_tiers');
    }
};
