<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentCategory;
use App\Models\IncidentStatus;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'incidentCategories' => IncidentCategory::query()->orderBy('name')->get(),
            'incidentStatuses' => IncidentStatus::query()->orderBy('sort_order')->get(),
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('name')->get(),
            'severityLevels' => Incident::SEVERITIES,
        ]);
    }
}
