<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureProjectClassificationColumns();
        $this->syncSystemsCatalog($this->currentSystemsCatalog());
    }

    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            $columnsToDrop = [];

            if (Schema::hasColumn('projects', 'linea')) {
                $columnsToDrop[] = 'linea';
            }

            if (Schema::hasColumn('projects', 'tipo')) {
                $columnsToDrop[] = 'tipo';
            }

            if ($columnsToDrop !== []) {
                Schema::table('projects', function (Blueprint $table) use ($columnsToDrop): void {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        $this->syncSystemsCatalog($this->legacySystemsCatalog());
    }

    private function ensureProjectClassificationColumns(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        $hasTipo = Schema::hasColumn('projects', 'tipo');
        $hasLinea = Schema::hasColumn('projects', 'linea');

        if ($hasTipo && $hasLinea) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) use ($hasTipo, $hasLinea): void {
            if (! $hasTipo) {
                $table->string('tipo')->nullable()->after('code');
            }

            if (! $hasLinea) {
                $table->string('linea')->nullable()->after('tipo');
            }
        });
    }

    private function syncSystemsCatalog(array $items): void
    {
        if (! Schema::hasTable('systems_catalog')) {
            return;
        }

        $timestamp = now();
        $payload = array_map(function (array $item) use ($timestamp): array {
            return [
                'code' => $item['code'],
                'name' => $item['name'],
                'category' => $item['category'],
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $items);

        DB::table('systems_catalog')->upsert(
            $payload,
            ['code'],
            ['name', 'category', 'is_active', 'updated_at']
        );

        DB::table('systems_catalog')
            ->whereNotIn('code', array_column($items, 'code'))
            ->update(['is_active' => false, 'updated_at' => $timestamp]);
    }

    private function currentSystemsCatalog(): array
    {
        return [
            ['code' => 'comedero_automatico', 'name' => 'Comedero Automatico', 'category' => 'alimentacion'],
            ['code' => 'bebedero_niple', 'name' => 'Bebedero Niple', 'category' => 'hidratacion'],
            ['code' => 'falso_techo', 'name' => 'Falso Techo', 'category' => 'estructura'],
            ['code' => 'cortina_lateral', 'name' => 'Cortina Lateral', 'category' => 'estructura'],
            ['code' => 'calefaccion', 'name' => 'Calefaccion', 'category' => 'climatizacion'],
            ['code' => 'silos', 'name' => 'Silos', 'category' => 'alimentacion'],
            ['code' => 'alimentacion', 'name' => 'Alimentacion', 'category' => 'alimentacion'],
            ['code' => 'ventiladores', 'name' => 'Ventiladores', 'category' => 'climatizacion'],
            ['code' => 'nebulizadores', 'name' => 'Nebulizadores', 'category' => 'climatizacion'],
            ['code' => 'iluminacion', 'name' => 'Iluminacion', 'category' => 'electrico'],
            ['code' => 'extractores', 'name' => 'Extractores', 'category' => 'climatizacion'],
            ['code' => 'panel_humedo', 'name' => 'Panel Humedo', 'category' => 'climatizacion'],
            ['code' => 'inlet', 'name' => 'Inlet', 'category' => 'climatizacion'],
            ['code' => 'tunel_door', 'name' => 'Tunel Door', 'category' => 'climatizacion'],
            ['code' => 'red_electrica', 'name' => 'Red Electrica', 'category' => 'electrico'],
            ['code' => 'tablero_control_potencia', 'name' => 'Tablero de Control y Potencia', 'category' => 'electrico'],
            ['code' => 'controlador', 'name' => 'Controlador', 'category' => 'control'],
            ['code' => 'sistema_pesaje', 'name' => 'Sistema Pesaje', 'category' => 'control'],
            ['code' => 'sistema_comunicacion', 'name' => 'Sistema Comunicacion', 'category' => 'control'],
            ['code' => 'aislamiento', 'name' => 'Aislamiento', 'category' => 'estructura'],
        ];
    }

    private function legacySystemsCatalog(): array
    {
        return [
            ['code' => 'falso_techo', 'name' => 'Falso Techo', 'category' => 'estructura'],
            ['code' => 'comedero', 'name' => 'Comedero', 'category' => 'alimentacion'],
            ['code' => 'bebedero', 'name' => 'Bebedero', 'category' => 'alimentacion'],
            ['code' => 'cortina_lateral', 'name' => 'Cortina Lateral', 'category' => 'estructura'],
            ['code' => 'alimentacion', 'name' => 'Sistema Alimentación', 'category' => 'alimentacion'],
            ['code' => 'panel_humedo', 'name' => 'Panel Húmedo', 'category' => 'climatizacion'],
            ['code' => 'extractor', 'name' => 'Extractor', 'category' => 'climatizacion'],
            ['code' => 'malla', 'name' => 'Malla', 'category' => 'estructura'],
            ['code' => 'calefaccion', 'name' => 'Calefacción', 'category' => 'climatizacion'],
            ['code' => 'sistema_electrico', 'name' => 'Sistema Eléctrico', 'category' => 'electrico'],
        ];
    }
};