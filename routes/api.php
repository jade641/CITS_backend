<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncidentAssignmentController;
use App\Http\Controllers\Api\IncidentAttachmentController;
use App\Http\Controllers\Api\IncidentCommentController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\IncidentStatusController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $dbStatus = 'unknown';
    $dbMessage = '';
    
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbStatus = 'connected';
        $dbMessage = 'Database connection successful';
    } catch (\Exception $e) {
        $dbStatus = 'error';
        $dbMessage = 'Database connection failed: ' . $e->getMessage();
    }
    
    return response()->json([
        'status' => 'ok',
        'application' => config('app.name'),
        'environment' => config('app.env'),
        'database' => [
            'status' => $dbStatus,
            'message' => $dbMessage,
        ],
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'store']);
    Route::post('/reset-password', [PasswordResetController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/lookups', [LookupController::class, 'index']);
    Route::get('/roles', [RoleController::class, 'index']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('incidents', IncidentController::class);

    // SOC workflow routes
    Route::patch('/incidents/{incident}/status', [IncidentController::class, 'updateStatus']);
    Route::post('/incidents/{incident}/timeline', [IncidentController::class, 'saveTimeline']);
    Route::post('/incidents/{incident}/iocs', [IncidentController::class, 'saveIocs']);
    Route::post('/incidents/{incident}/affected-systems', [IncidentController::class, 'saveAffectedSystems']);
    Route::post('/incidents/{incident}/actions-taken', [IncidentController::class, 'saveActionsTaken']);
    Route::post('/incidents/{incident}/severity', [IncidentController::class, 'saveSeverity']);
    Route::post('/incidents/{incident}/evidence', [IncidentController::class, 'uploadEvidence']);
    Route::post('/incidents/{incident}/findings', [IncidentController::class, 'saveFindings']);
    Route::post('/incidents/{incident}/remediation-actions', [IncidentController::class, 'saveRemediationActions']);
    Route::post('/incidents/{incident}/submit-resolution', [IncidentController::class, 'submitResolution']);
    Route::get('/incidents/{incident}/audit-log', [IncidentController::class, 'getAuditLog']);

    Route::post('/incidents/{incident}/assignments', [IncidentAssignmentController::class, 'store']);
    Route::post('/incidents/{incident}/status', [IncidentStatusController::class, 'store']);
    Route::post('/incidents/{incident}/comments', [IncidentCommentController::class, 'store']);
    Route::post('/incidents/{incident}/attachments', [IncidentAttachmentController::class, 'store']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::post('/reports/export/csv', [ReportController::class, 'exportCsv']);
    Route::post('/reports/export/pdf', [ReportController::class, 'exportPdf']);

    Route::get('/analytics', AnalyticsController::class);
});
