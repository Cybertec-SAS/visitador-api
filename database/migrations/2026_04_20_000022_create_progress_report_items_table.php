<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_report_id')->constrained()->cascadeOnDelete();
            $table->string('section_name')->nullable();
            $table->string('activity_code')->nullable();
            $table->string('activity_name');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->decimal('advance_percent', 5, 2)->default(0);
            $table->decimal('pending_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_report_items');
    }
};
