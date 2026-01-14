<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectHistory;
use Illuminate\Support\Facades\Auth;

class ProjectHistoryService
{
    public function recordEvent(
        Project $project,
        string $eventType,
        string $title,
        ?string $description = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ProjectHistory {
        return ProjectHistory::create([
            'project_id' => $project->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => Auth::id(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    public function recordProjectCreated(Project $project): ProjectHistory
    {
        return $this->recordEvent(
            $project,
            'created',
            'Project Created',
            "Project '{$project->name}' was created with code {$project->project_code}",
            null,
            $project->status,
            Project::class,
            $project->id
        );
    }

    public function recordStatusChange(Project $project, string $oldStatus, string $newStatus): ProjectHistory
    {
        $statusNames = [
            'planning' => 'Planning',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $oldStatusName = $statusNames[$oldStatus] ?? $oldStatus;
        $newStatusName = $statusNames[$newStatus] ?? $newStatus;

        return $this->recordEvent(
            $project,
            'status_changed',
            'Status Changed',
            "Project status changed from '{$oldStatusName}' to '{$newStatusName}'",
            $oldStatus,
            $newStatus,
            Project::class,
            $project->id
        );
    }

    public function recordProjectUpdated(Project $project, array $changes): void
    {
        foreach ($changes as $field => $value) {
            if (in_array($field, ['name', 'description', 'project_manager_id', 'start_date', 'end_date', 'budget', 'notes'])) {
                $this->recordEvent(
                    $project,
                    'updated',
                    ucfirst(str_replace('_', ' ', $field)) . ' Updated',
                    "Project {$field} was updated",
                    null,
                    is_array($value) ? json_encode($value) : (string)$value,
                    Project::class,
                    $project->id
                );
            }
        }
    }

    public function recordRelatedEvent(
        Project $project,
        string $eventType,
        string $title,
        string $description,
        string $referenceType,
        int $referenceId
    ): ProjectHistory {
        return $this->recordEvent(
            $project,
            $eventType,
            $title,
            $description,
            null,
            null,
            $referenceType,
            $referenceId
        );
    }
}

