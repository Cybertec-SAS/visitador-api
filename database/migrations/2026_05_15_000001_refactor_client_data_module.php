<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropFarmNeighborColumns();
        $this->expandFarmVoltageOptions();
        $this->dropProgressReportVisitReference();
        $this->normalizeStructureHierarchy();
        $this->uppercaseActiveModuleData();
        $this->backfillFarmGalponCounts();
    }

    public function down(): void
    {
        $this->restoreFarmNeighborColumns();
        $this->restoreProgressReportVisitReference();
        $this->shrinkFarmVoltageOptions();
    }

    private function dropFarmNeighborColumns(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        $columnsToDrop = [];

        foreach (['distance_to_neighbor_boundary_m', 'neighboring_properties_notes'] as $column) {
            if (Schema::hasColumn('farms', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('farms', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }

    private function restoreFarmNeighborColumns(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        Schema::table('farms', function (Blueprint $table): void {
            if (! Schema::hasColumn('farms', 'distance_to_neighbor_boundary_m')) {
                $table->decimal('distance_to_neighbor_boundary_m', 10, 2)->nullable()->after('is_transformator_feeds_other_installations');
            }

            if (! Schema::hasColumn('farms', 'neighboring_properties_notes')) {
                $table->string('neighboring_properties_notes')->nullable()->after('transformator_are_feeding_installations');
            }
        });
    }

    private function expandFarmVoltageOptions(): void
    {
        if (! Schema::hasTable('farms') || ! Schema::hasColumn('farms', 'farm_voltage')) {
            return;
        }

        Schema::table('farms', function (Blueprint $table): void {
            $table->string('farm_voltage', 10)->nullable()->change();
        });
    }

    private function shrinkFarmVoltageOptions(): void
    {
        if (Schema::hasTable('farms') && Schema::hasColumn('farms', 'farm_voltage')) {
            DB::table('farms')->where('farm_voltage', '440V')->update(['farm_voltage' => null]);
        }

        if (! Schema::hasTable('farms') || ! Schema::hasColumn('farms', 'farm_voltage')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('farms', function (Blueprint $table): void {
                $table->string('farm_voltage', 10)->nullable()->change();
            });

            return;
        }

        DB::statement("ALTER TABLE farms MODIFY farm_voltage ENUM('110V', '220V') NULL");
    }

    private function dropProgressReportVisitReference(): void
    {
        if (! Schema::hasTable('progress_reports') || ! Schema::hasColumn('progress_reports', 'visit_id')) {
            return;
        }

        Schema::table('progress_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('visit_id');
        });
    }

    private function restoreProgressReportVisitReference(): void
    {
        if (! Schema::hasTable('progress_reports') || Schema::hasColumn('progress_reports', 'visit_id')) {
            return;
        }

        Schema::table('progress_reports', function (Blueprint $table): void {
            $table->foreignId('visit_id')->nullable()->after('project_id')->constrained('visits')->nullOnDelete();
        });
    }

    private function normalizeStructureHierarchy(): void
    {
        if (! Schema::hasTable('structures') || ! Schema::hasColumn('structures', 'structure_type')) {
            return;
        }

        DB::table('structures')->whereNull('parent_structure_id')->update(['structure_type' => 'GALPON']);
        DB::table('structures')->whereNotNull('parent_structure_id')->update(['structure_type' => 'SYSTEM']);
    }

    private function uppercaseActiveModuleData(): void
    {
        $this->uppercaseColumns('clients', ['razon_social', 'nit']);
        $this->uppercaseColumns('farms', ['nombre', 'access_ways', 'observations', 'transformator_are_feeding_installations']);
        $this->uppercaseColumns('farm_contacts', ['name']);
        $this->uppercaseColumns('farm_georreferences', ['address', 'town', 'department']);
        $this->uppercaseColumns('structures', ['name', 'code', 'description', 'observations']);
        $this->uppercaseColumns('systems_catalog', ['code', 'name', 'category']);
    }

    private function uppercaseColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $grammar = DB::connection()->getQueryGrammar();

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $wrappedColumn = $grammar->wrap($column);

            DB::table($table)
                ->whereNotNull($column)
                ->update([$column => DB::raw("UPPER({$wrappedColumn})")]);
        }
    }

    private function backfillFarmGalponCounts(): void
    {
        if (! Schema::hasTable('farms')
            || ! Schema::hasTable('structures')
            || ! Schema::hasColumn('farms', 'total_galpones')) {
            return;
        }

        DB::table('farms')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($farms): void {
                foreach ($farms as $farm) {
                    $count = DB::table('structures')
                        ->where('farm_id', $farm->id)
                        ->where('structure_type', 'GALPON')
                        ->whereNull('parent_structure_id')
                        ->count();

                    DB::table('farms')
                        ->where('id', $farm->id)
                        ->update(['total_galpones' => $count]);
                }
            });
    }
};
