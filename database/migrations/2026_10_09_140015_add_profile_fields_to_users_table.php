<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('locale', 5)->default('pt')->after('avatar_path');
            $table->string('nationality', 2)->nullable()->comment('ISO 3166-1 alpha-2')->after('locale');
            $table->text('bio')->nullable()->after('locale');
            $table->boolean('marketing_emails')->default(true)->after('bio')->comment('Opt-in to weekly digest / nudges');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'avatar_path', 'locale', 'nationality', 'bio', 'marketing_emails']);
        });
    }
};
