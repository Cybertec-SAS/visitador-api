<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('farms', 'galpones_a_cotizar')) {
            Schema::table('farms', function (Blueprint $table): void {
                $table->dropColumn('galpones_a_cotizar');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('farms', 'galpones_a_cotizar')) {
            Schema::table('farms', function (Blueprint $table): void {
                $table->unsignedInteger('galpones_a_cotizar')->nullable()->after('total_galpones');
            });
        }
    }
};
