<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('systems_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('systems_catalog')->insert($this->systemsCatalogSeed());
    }

    public function down(): void
    {
        Schema::dropIfExists('systems_catalog');
    }

    private function systemsCatalogSeed(): array
    {
        $timestamp = now();

        return [
            ['code' => 'comedero_automatico', 'name' => 'Comedero Automatico', 'category' => 'alimentacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'bebedero_niple', 'name' => 'Bebedero Niple', 'category' => 'hidratacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'falso_techo', 'name' => 'Falso Techo', 'category' => 'estructura', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'cortina_lateral', 'name' => 'Cortina Lateral', 'category' => 'estructura', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'calefaccion', 'name' => 'Calefaccion', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'silos', 'name' => 'Silos', 'category' => 'alimentacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'alimentacion', 'name' => 'Alimentacion', 'category' => 'alimentacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'ventiladores', 'name' => 'Ventiladores', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'nebulizadores', 'name' => 'Nebulizadores', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'iluminacion', 'name' => 'Iluminacion', 'category' => 'electrico', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'extractores', 'name' => 'Extractores', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'panel_humedo', 'name' => 'Panel Humedo', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'inlet', 'name' => 'Inlet', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'tunel_door', 'name' => 'Tunel Door', 'category' => 'climatizacion', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'red_electrica', 'name' => 'Red Electrica', 'category' => 'electrico', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'tablero_control_potencia', 'name' => 'Tablero de Control y Potencia', 'category' => 'electrico', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'controlador', 'name' => 'Controlador', 'category' => 'control', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'sistema_pesaje', 'name' => 'Sistema Pesaje', 'category' => 'control', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'sistema_comunicacion', 'name' => 'Sistema Comunicacion', 'category' => 'control', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['code' => 'aislamiento', 'name' => 'Aislamiento', 'category' => 'estructura', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];
    }
};
