<?php

namespace App\Traits;

use App\Services\AuditLogService;

trait LogsAudit
{
    protected function auditLog(): AuditLogService
    {
        return app(AuditLogService::class);
    }

    /**
     * Log a created action
     */
    protected function logCreated($model, ?string $description = null)
    {
        return $this->auditLog()->logCreated($model, $description);
    }

    /**
     * Log an updated action
     */
    protected function logUpdated($model, array $oldValues, ?string $description = null)
    {
        return $this->auditLog()->logUpdated($model, $oldValues, $description);
    }

    /**
     * Log a deleted action
     */
    protected function logDeleted($model, ?string $description = null)
    {
        return $this->auditLog()->logDeleted($model, $description);
    }

    /**
     * Log a custom action
     */
    protected function logAction(string $action, $model, ?string $description = null, ?array $additionalData = null)
    {
        return $this->auditLog()->logAction($action, $model, $description, $additionalData);
    }

    /**
     * Log approval
     */
    protected function logApproved($model, ?string $description = null)
    {
        return $this->auditLog()->logApproved($model, $description);
    }

    /**
     * Log rejection
     */
    protected function logRejected($model, ?string $reason = null)
    {
        return $this->auditLog()->logRejected($model, $reason);
    }

    /**
     * Log cancellation
     */
    protected function logCancelled($model, ?string $reason = null)
    {
        return $this->auditLog()->logCancelled($model, $reason);
    }
}

