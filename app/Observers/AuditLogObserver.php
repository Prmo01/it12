<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    protected $auditLogService;
    protected $dirtyAttributes = [];

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        // Skip logging if model is AuditLog itself to prevent infinite loops
        if ($model instanceof AuditLog) {
            return;
        }

        $modelName = class_basename($model);
        $description = "Created new {$modelName}";
        
        // Add specific descriptions based on model
        if (method_exists($model, 'getAuditDescription')) {
            $description = $model->getAuditDescription('created');
        } else {
            // Try to get a meaningful identifier
            if (isset($model->name)) {
                $description .= ": {$model->name}";
            } elseif (isset($model->item_code)) {
                $description .= ": {$model->item_code}";
            } elseif (isset($model->pr_number)) {
                $description .= ": {$model->pr_number}";
            } elseif (isset($model->po_number)) {
                $description .= ": {$model->po_number}";
            } elseif (isset($model->gr_number)) {
                $description .= ": {$model->gr_number}";
            } elseif (isset($model->quotation_number)) {
                $description .= ": {$model->quotation_number}";
            } elseif (isset($model->project_code)) {
                $description .= ": {$model->project_code}";
            }
        }

        $this->auditLogService->logCreated($model, $description);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Skip logging if model is AuditLog itself
        if ($model instanceof AuditLog) {
            return;
        }

        $modelName = class_basename($model);
        
        // Get changed attributes
        $oldValues = [];
        $newValues = [];
        
        foreach ($model->getDirty() as $key => $value) {
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $value;
        }

        // Only log if there are actual changes
        if (empty($oldValues)) {
            return;
        }

        $description = "Updated {$modelName}";
        
        // Add specific descriptions
        if (method_exists($model, 'getAuditDescription')) {
            $description = $model->getAuditDescription('updated');
        } else {
            if (isset($model->name)) {
                $description .= ": {$model->name}";
            } elseif (isset($model->item_code)) {
                $description .= ": {$model->item_code}";
            } elseif (isset($model->pr_number)) {
                $description .= ": {$model->pr_number}";
            } elseif (isset($model->po_number)) {
                $description .= ": {$model->po_number}";
            }
        }

        $this->auditLogService->logUpdated($model, $oldValues, $description);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        // Skip logging if model is AuditLog itself
        if ($model instanceof AuditLog) {
            return;
        }

        $modelName = class_basename($model);
        $description = "Deleted {$modelName}";
        
        if (method_exists($model, 'getAuditDescription')) {
            $description = $model->getAuditDescription('deleted');
        } else {
            if (isset($model->name)) {
                $description .= ": {$model->name}";
            } elseif (isset($model->item_code)) {
                $description .= ": {$model->item_code}";
            }
        }

        $this->auditLogService->logDeleted($model, $description);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        // Skip logging if model is AuditLog itself
        if ($model instanceof AuditLog) {
            return;
        }

        $modelName = class_basename($model);
        $description = "Restored {$modelName}";
        
        if (isset($model->name)) {
            $description .= ": {$model->name}";
        }

        $this->auditLogService->logRestored($model, $description);
    }
}

