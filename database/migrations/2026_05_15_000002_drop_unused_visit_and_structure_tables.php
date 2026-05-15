<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropStructureReferenceFromProgressItems();

        Schema::disableForeignKeyConstraints();

        foreach ($this->tablesToDrop() as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversible cleanup: dropped tables are intentionally not recreated here.
    }

    private function dropStructureReferenceFromProgressItems(): void
    {
        if (! Schema::hasTable('progress_report_items') || ! Schema::hasColumn('progress_report_items', 'structure_id')) {
            return;
        }

        Schema::table('progress_report_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('structure_id');
        });
    }

    private function tablesToDrop(): array
    {
        return [
            'generated_reports',
            'report_templates',
            'visit_material_requests',
            'visit_measurements',
            'visit_system_reviews',
            'visit_form_responses',
            'form_fields',
            'form_sections',
            'form_templates',
            'visit_media_annotations',
            'visit_media',
            'visit_commitments',
            'visit_findings',
            'visit_signatures',
            'visit_participants',
            'visit_structures',
            'project_structures',
            'visits',
            'visit_types',
            'structures',
        ];
    }
};
