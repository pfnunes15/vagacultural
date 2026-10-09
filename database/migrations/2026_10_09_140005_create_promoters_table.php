<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('The promoter account (role: promoter)')->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->comment('Parent organization that manages this promoter, if any')->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('auto_publish')->default(false)
                ->comment('Admin-set trust: when true, submitted events are published immediately');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoters');
    }
};
