<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ChangeOrder;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    protected $historyService;

    public function __construct(ProjectHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create($data);
            $this->historyService->recordProjectCreated($project);
            return $project;
        });
    }

    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $oldStatus = $project->status;
            $changes = [];
            
            foreach ($data as $key => $value) {
                if ($project->$key != $value) {
                    $changes[$key] = $value;
                }
            }
            
            $project->update($data);
            
            // Record status change if status was updated
            if (isset($data['status']) && $oldStatus !== $data['status']) {
                $this->historyService->recordStatusChange($project, $oldStatus, $data['status']);
            }
            
            // Record other updates
            if (!empty($changes)) {
                $this->historyService->recordProjectUpdated($project, $changes);
            }
            
            return $project->fresh();
        });
    }

    public function createChangeOrder(array $data): ChangeOrder
    {
        return DB::transaction(function () use ($data) {
            $changeOrder = ChangeOrder::create($data);
            
            // Update project timeline if approved
            if ($changeOrder->status === 'approved' && $changeOrder->additional_days > 0) {
                $project = $changeOrder->project;
                $project->end_date = $project->end_date->addDays($changeOrder->additional_days);
                $project->budget += $changeOrder->additional_cost;
                $project->save();
            }
            
            return $changeOrder;
        });
    }

    public function approveChangeOrder(ChangeOrder $changeOrder, int $approvedBy): ChangeOrder
    {
        return DB::transaction(function () use ($changeOrder, $approvedBy) {
            $changeOrder->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            // Update project
            $project = $changeOrder->project;
            if ($changeOrder->additional_days > 0) {
                $project->end_date = $project->end_date->addDays($changeOrder->additional_days);
            }
            $project->budget += $changeOrder->additional_cost;
            $project->save();

            return $changeOrder->fresh();
        });
    }

    public function getHistoryService(): ProjectHistoryService
    {
        return $this->historyService;
    }
}

