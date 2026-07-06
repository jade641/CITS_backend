<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function getSlugAttribute(string $value): string
    {
        return $value === 'in-progress' ? 'in_progress' : $value;
    }

    public function setSlugAttribute(string $value): void
    {
        $this->attributes['slug'] = $value === 'in-progress' ? 'in_progress' : $value;
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'status_id');
    }
}
