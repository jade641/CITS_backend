<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentIoc extends Model
{
    use HasFactory;

    protected $table = 'incident_iocs';

    protected $fillable = [
        'incident_id',
        'type',
        'value',
        'description',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
