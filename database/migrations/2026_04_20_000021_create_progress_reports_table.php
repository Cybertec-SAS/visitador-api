<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('report_number');
            $table->date('cutoff_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('weighted_progress_percent', 5, 2)->default(0);
            $table->decimal('scheduled_progress_percent', 5, 2)->default(0);
            $table->decimal('difference_percent', 5, 2)->default(0);
            $table->unsignedInteger('contract_days')->nullable();
            $table->unsignedInteger('elapsed_days')->nullable();
            $table->unsignedInteger('remaining_days')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
