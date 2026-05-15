<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Structure extends Model
{
    use HasFactory;

    public const TYPE_GALPON = 'GALPON';
    public const TYPE_SYSTEM = 'SYSTEM';

    protected $fillable = [
        'farm_id',
        'parent_structure_id',
        'structure_type',
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
        static::deleting(function (Structure $structure): void {
            if (! $structure->isGalpon()) {
                return;
            }

            $structure->systems()->get()->each->delete();
        });

        static::saved(function (Structure $structure): void {
            $structure->syncFarmGalponCounts();
        });

        static::deleted(function (Structure $structure): void {
            $structure->syncFarmGalponCounts();
        });
    }

    public static function allowedTypes(): array
    {
        return [self::TYPE_GALPON, self::TYPE_SYSTEM];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'parent_structure_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Structure::class, 'parent_structure_id');
    }

    public function systems(): HasMany
    {
        return $this->hasMany(Structure::class, 'parent_structure_id')
            ->where('structure_type', self::TYPE_SYSTEM)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function isGalpon(): bool
    {
        return $this->structure_type === self::TYPE_GALPON;
    }

    protected function syncFarmGalponCounts(): void
    {
        $farmIds = array_filter([
            $this->farm_id,
            $this->getOriginal('farm_id'),
        ]);

        foreach (array_unique($farmIds) as $farmId) {
            $farm = Farm::query()->find($farmId);

            if ($farm) {
                $farm->syncTotalGalponesFromStructures();
            }
        }
    }
}
