<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_by',
        'name',
        'type',
        'format',
        'status',
        'filters',
        'summary',
        'file_path',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'summary' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
