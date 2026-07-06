<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    public const SEVERITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'severity',
        'category_id',
        'status_id',
        'reporter_id',
        'current_assignee_id',
        'affected_asset',
        'source_ip',
        'location',
        'impact_summary',
        'resolution_notes',
        'occurred_at',
        'reported_at',
        'resolved_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncidentCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(IncidentStatus::class, 'status_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function currentAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(IncidentAssignment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IncidentComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(IncidentAttachment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(IncidentHistory::class);
    }
}
