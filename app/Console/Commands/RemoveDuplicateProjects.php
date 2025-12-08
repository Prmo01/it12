<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:remove-duplicates {--dry-run : Show duplicates without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate projects with names like "Construction Project 1", "Construction Project 2", etc.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Searching for duplicate "Construction Project" projects...');

        // Find all projects matching the pattern
        $projects = Project::where('name', 'like', 'Construction Project%')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($projects->isEmpty()) {
            $this->info('No "Construction Project" projects found.');
            return 0;
        }

        // Group by project number (extract number from name)
        $grouped = [];
        foreach ($projects as $project) {
            // Extract number from "Construction Project X" or "Construction Project X Y"
            if (preg_match('/Construction Project\s+(\d+)/', $project->name, $matches)) {
                $number = (int)$matches[1];
                if (!isset($grouped[$number])) {
                    $grouped[$number] = [];
                }
                $grouped[$number][] = $project;
            }
        }

        if (empty($grouped)) {
            $this->info('No projects matching the pattern found.');
            return 0;
        }

        $this->warn("Found projects grouped by number:");
        $this->newLine();

        $totalToDelete = 0;
        $keptProjects = [];

        foreach ($grouped as $number => $projectGroup) {
            $count = count($projectGroup);
            $this->line("  • Construction Project {$number} ({$count} project(s))");

            if ($count > 1) {
                // Keep the first (oldest) project
                $keepProject = $projectGroup[0];
                $projectsToDelete = array_slice($projectGroup, 1);

                $this->line("    Keeping: {$keepProject->project_code} - {$keepProject->name} (created: {$keepProject->created_at->format('Y-m-d H:i:s')})");
                $keptProjects[] = $keepProject;

                foreach ($projectsToDelete as $project) {
                    $totalToDelete++;
                    if ($isDryRun) {
                        $this->line("    Would delete: {$project->project_code} - {$project->name} (created: {$project->created_at->format('Y-m-d H:i:s')})");
                    } else {
                        // Check if project has related records
                        $hasRelations = $project->changeOrders()->count() > 0
                            || $project->purchaseRequests()->count() > 0
                            || $project->materialIssuances()->count() > 0;

                        if ($hasRelations) {
                            $this->warn("    Skipping {$project->project_code} - has related records (soft delete instead)");
                            $project->delete(); // Soft delete
                        } else {
                            $this->line("    Deleting: {$project->project_code} - {$project->name}");
                            $project->forceDelete(); // Hard delete
                        }
                    }
                }
            } else {
                // Only one project with this number, keep it
                $this->line("    Keeping: {$projectGroup[0]->project_code} - {$projectGroup[0]->name} (only one)");
                $keptProjects[] = $projectGroup[0];
            }
            $this->newLine();
        }

        // Show summary
        $this->info("Summary:");
        $this->line("  Total unique project numbers: " . count($grouped));
        $this->line("  Projects to keep: " . count($keptProjects));
        
        if ($isDryRun) {
            $this->info("DRY RUN: Would delete {$totalToDelete} duplicate project(s).");
            $this->info("Run without --dry-run to actually delete them.");
        } else {
            $this->info("Successfully removed {$totalToDelete} duplicate project(s).");
            $this->info("Kept " . count($keptProjects) . " unique project(s) numbered 1-" . count($keptProjects) . ".");
        }

        return 0;
    }
}
