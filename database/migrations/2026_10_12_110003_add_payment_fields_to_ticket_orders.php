<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table): void {
            $table->decimal('commission_amount', 10, 2)->default(0)->after('total')
                ->comment('Platform commission retained on this order');
            $table->string('currency', 3)->default('EUR')->after('commission_amount');
            $table->string('payment_status', 16)->default('free')->after('currency')
                ->comment('free|pending|paid|expired|refunded');
            $table->string('payment_intent_id')->nullable()->after('payment_status');
            $table->timestamp('reserved_until')->nullable()->after('payment_intent_id')
                ->comment('Inventory hold expiry while payment is pending');
            $table->timestamp('paid_at')->nullable()->after('reserved_until');

            $table->index('payment_status');
            $table->index('reserved_until');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table): void {
            $table->dropColumn(['commission_amount', 'currency', 'payment_status', 'payment_intent_id', 'reserved_until', 'paid_at']);
        });
    }
};
