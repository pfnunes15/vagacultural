<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promoters', function (Blueprint $table): void {
            $table->decimal('commission_rate', 5, 2)->nullable()
                ->comment('Platform commission % for this promoter (overrides default)');
            $table->string('stripe_account_id')->nullable()->comment('Stripe Connect account');
            $table->boolean('stripe_onboarded')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('promoters', function (Blueprint $table): void {
            $table->dropColumn(['commission_rate', 'stripe_account_id', 'stripe_onboarded']);
        });
    }
};
