<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Galpon extends Model
{
    use HasFactory;

    protected $table = 'galpones';

    protected $fillable = [
        'farm_id',
        'name',
        'code',
        'status',
        'description',
        'dimensions_json',
        'technical_attributes_json',
        'observations',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'dimensions_json' => 'array',
            'technical_attributes_json' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Galpon $galpon): void {
            $galpon->farm?->refreshTotalGalpones();
        });

        static::deleted(function (Galpon $galpon): void {
            $galpon->farm?->refreshTotalGalpones();
        });
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function systems(): HasMany
    {
        return $this->hasMany(GalponSystem::class)->orderBy('id');
    }
}
