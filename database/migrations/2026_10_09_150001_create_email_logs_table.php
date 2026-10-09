<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 48)->comment('e.g. registration_verification');
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->string('status', 16)->default('sent');
            $table->foreignId('sent_by')->nullable()->comment('Admin who triggered a resend, if any')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
