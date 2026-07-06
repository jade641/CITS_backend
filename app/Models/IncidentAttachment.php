<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'original_name',
        'stored_name',
        'disk',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
