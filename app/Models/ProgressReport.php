<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'report_number',
        'cutoff_date',
        'start_date',
        'end_date',
        'weighted_progress_percent',
        'scheduled_progress_percent',
        'difference_percent',
        'contract_days',
        'elapsed_days',
        'remaining_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cutoff_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProgressReportItem::class);
    }

    public function curvePoints(): HasMany
    {
        return $this->hasMany(ProgressCurvePoint::class);
    }
}
