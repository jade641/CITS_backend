<?php

namespace App\Http\Requests\Incident;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => $this->input('description') ?? '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(Incident::SEVERITIES)],
            'category_id' => ['required', 'integer', 'exists:incident_categories,id'],
            'affected_asset' => ['nullable', 'string', 'max:255'],
            'source_ip' => ['nullable', 'ip'],
            'location' => ['nullable', 'string', 'max:255'],
            'impact_summary' => ['nullable', 'string'],
            'occurred_at' => ['required', 'date'],
        ];
    }
}
