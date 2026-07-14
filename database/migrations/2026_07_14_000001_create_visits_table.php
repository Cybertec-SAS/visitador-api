<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('diagnostico_tecnico');
            $table->string('status')->default('draft');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('galpon_id')->constrained('galpones')->cascadeOnDelete();
            $table->date('fecha');
            $table->unsignedInteger('num_aves')->nullable();
            $table->unsignedInteger('dia_lote')->nullable();
            $table->string('cliente_nombre')->nullable();
            $table->string('granja_nombre')->nullable();
            $table->string('galpon_numero')->nullable();
            $table->string('ubicacion')->nullable();
            $table->unsignedInteger('total_galpones')->nullable();
            $table->json('contacto_json')->nullable();
            $table->json('control_json')->nullable();
            $table->json('tablero_json')->nullable();
            $table->json('variables_json')->nullable();
            $table->json('ventilacion_json')->nullable();
            $table->json('mecanicos_json')->nullable();
            $table->json('evidencia_json')->nullable();
            $table->json('informe_json')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'farm_id', 'status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
