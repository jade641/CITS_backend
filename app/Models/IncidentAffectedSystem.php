<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentAffectedSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'asset_name',
        'asset_type',
        'impact_level',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
