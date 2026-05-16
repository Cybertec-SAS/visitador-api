<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalponSystem extends Model
{
    use HasFactory;

    protected $table = 'galpon_systems';

    protected $fillable = [
        'galpon_id',
        'system_id',
        'quantity',
        'notes',
        'technical_attributes_json',
    ];

    protected function casts(): array
    {
        return [
            'technical_attributes_json' => 'array',
        ];
    }

    public function galpon(): BelongsTo
    {
        return $this->belongsTo(Galpon::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(SystemsCatalog::class, 'system_id');
    }
}
