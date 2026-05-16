<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galpon_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('galpon_id')->constrained('galpones')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems_catalog')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->json('technical_attributes_json')->nullable();
            $table->timestamps();

            $table->unique(['galpon_id', 'system_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galpon_systems');
    }
};