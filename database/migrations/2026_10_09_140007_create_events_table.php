<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promoter_id')->comment('Every event is owned by a promoter; the organization is reached via the promoter')->constrained('promoters')->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('title');
            $table->string('slug')->unique();
            $table->json('summary')->nullable();
            $table->json('description')->nullable();

            $table->string('status', 16)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('cover_image_path')->nullable();

            $table->boolean('is_free')->default(false);
            $table->unsignedTinyInteger('min_age')->nullable()->comment('Minimum age rating (0,3,6,12,14,16,18)');
            $table->decimal('price_from', 8, 2)->nullable();
            $table->string('ticket_url')->nullable();
            $table->string('website')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
