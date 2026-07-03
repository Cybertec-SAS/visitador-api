<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nombre',
        'transformator_capacity_kva',
        'access_ways',
        'observations',
        'farm_voltage',
        'farm_electric_current',
        'have_own_transformator',
        'is_transformator_feeds_other_installations',
        'transformator_are_feeding_installations',
        'have_easy_access_for_trailer',
        'staff_availability',
        'has_storage_warehouse',
        'how_many_warehouses',
        'total_galpones',
    ];

    protected function casts(): array
    {
        return [
            'have_own_transformator' => 'boolean',
            'is_transformator_feeds_other_installations' => 'boolean',
            'have_easy_access_for_trailer' => 'boolean',
            'staff_availability' => 'boolean',
            'has_storage_warehouse' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function georreference(): HasOne
    {
        return $this->hasOne(FarmGeorreference::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(FarmContact::class);
    }

    public function galpones(): HasMany
    {
        return $this->hasMany(Galpon::class)->orderBy('sort_order')->orderBy('name');
    }

    public function refreshTotalGalpones(): void
    {
        $this->forceFill([
            'total_galpones' => $this->galpones()->count(),
        ])->saveQuietly();
    }
}
