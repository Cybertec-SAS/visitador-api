<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        $hasTotalGalpones = Schema::hasColumn('farms', 'total_galpones');
        $hasGalponesACotizar = Schema::hasColumn('farms', 'galpones_a_cotizar');

        if (! $hasTotalGalpones || ! $hasGalponesACotizar) {
            Schema::table('farms', function (Blueprint $table) use ($hasTotalGalpones, $hasGalponesACotizar): void {
                if (! $hasTotalGalpones) {
                    $table->unsignedInteger('total_galpones')->nullable()->after('has_storage_warehouse');
                }

                if (! $hasGalponesACotizar) {
                    $table->unsignedInteger('galpones_a_cotizar')->nullable()->after('total_galpones');
                }
            });
        }

        if (Schema::hasColumn('farms', 'farm_voltage')) {
            Schema::table('farms', function (Blueprint $table): void {
                $table->string('farm_voltage', 10)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        Schema::table('farms', function (Blueprint $table): void {
            $columnsToDrop = [];

            if (Schema::hasColumn('farms', 'galpones_a_cotizar')) {
                $columnsToDrop[] = 'galpones_a_cotizar';
            }

            if (Schema::hasColumn('farms', 'total_galpones')) {
                $columnsToDrop[] = 'total_galpones';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
