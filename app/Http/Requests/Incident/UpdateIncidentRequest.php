<?php

namespace App\Http\Requests\Incident;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'severity' => ['sometimes', 'required', 'string', Rule::in(Incident::SEVERITIES)],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:incident_categories,id'],
            'affected_asset' => ['nullable', 'string', 'max:255'],
            'source_ip' => ['nullable', 'ip'],
            'location' => ['nullable', 'string', 'max:255'],
            'impact_summary' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'occurred_at' => ['sometimes', 'required', 'date'],
        ];
    }
}
