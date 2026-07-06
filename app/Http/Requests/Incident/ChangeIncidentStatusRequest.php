<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;

class ChangeIncidentStatusRequest extends FormRequest
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
            'status_id' => ['required', 'integer', 'exists:incident_statuses,id'],
            'resolution_notes' => ['nullable', 'string'],
        ];
    }
}
