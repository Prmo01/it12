<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ChangeOrder;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create($data);
            return $project;
        });
    }

    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
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
}

