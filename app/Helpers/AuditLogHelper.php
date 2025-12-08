<?php

/**
 * Helper function to automatically log model changes with better descriptions
 */
if (!function_exists('logModelChange')) {
    function logModelChange($model, $action, $description = null) {
        $auditLogService = app(\App\Services\AuditLogService::class);
        
        switch($action) {
            case 'approved':
                return $auditLogService->logApproved($model, $description);
            case 'rejected':
                return $auditLogService->logRejected($model, $description);
            case 'cancelled':
                return $auditLogService->logCancelled($model, $description);
            default:
                return $auditLogService->logAction($action, $model, $description);
        }
    }
}

