<?php

namespace App\Http\Requests\Report;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateReportRequest extends FormRequest
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
            'type' => ['sometimes', 'string', 'max:50'],
            'format' => ['sometimes', 'string', Rule::in(['csv', 'pdf'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status_ids' => ['nullable', 'array'],
            'status_ids.*' => ['integer', 'exists:incident_statuses,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:incident_categories,id'],
            'severities' => ['nullable', 'array'],
            'severities.*' => ['string', Rule::in(Incident::SEVERITIES)],
            'reporter_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
