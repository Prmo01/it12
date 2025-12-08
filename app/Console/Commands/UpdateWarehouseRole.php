<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateWarehouseRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:update-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update quality_control role to warehouse_manager and migrate users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating warehouse manager role...');

        // Check if quality_control role exists
        $oldRole = Role::where('slug', 'quality_control')->first();
        
        // Check if warehouse_manager role exists
        $newRole = Role::where('slug', 'warehouse_manager')->first();

        if ($oldRole && !$newRole) {
            // Update the role
            $this->info("Updating role from 'quality_control' to 'warehouse_manager'...");
            $oldRole->update([
                'name' => 'Warehouse Manager',
                'slug' => 'warehouse_manager',
                'description' => 'Manages warehouse operations, inspects goods before approval and flags defective items'
            ]);
            $this->info("✓ Role updated successfully");
        } elseif (!$newRole) {
            // Create new role if it doesn't exist
            $this->info("Creating 'warehouse_manager' role...");
            Role::create([
                'name' => 'Warehouse Manager',
                'slug' => 'warehouse_manager',
                'description' => 'Manages warehouse operations, inspects goods before approval and flags defective items'
            ]);
            $this->info("✓ Role created successfully");
        } else {
            $this->info("✓ Warehouse manager role already exists");
        }

        // Update users with old role
        if ($oldRole) {
            $users = User::where('role_id', $oldRole->id)->get();
            if ($users->count() > 0) {
                $newRole = Role::where('slug', 'warehouse_manager')->first();
                if ($newRole) {
                    foreach ($users as $user) {
                        $user->update(['role_id' => $newRole->id]);
                        $this->info("✓ Updated user: {$user->email}");
                    }
                }
            }
        }

        // Delete old role if it still exists
        if ($oldRole && $oldRole->slug === 'quality_control') {
            $oldRole->delete();
            $this->info("✓ Deleted old 'quality_control' role");
        }

        // Delete viewer role if it exists
        $viewerRole = Role::where('slug', 'viewer')->first();
        if ($viewerRole) {
            // Update users first
            $users = User::where('role_id', $viewerRole->id)->get();
            foreach ($users as $user) {
                // Assign to admin as fallback (you may want to handle this differently)
                $adminRole = Role::where('slug', 'admin')->first();
                if ($adminRole) {
                    $user->update(['role_id' => $adminRole->id]);
                    $this->warn("⚠ Moved viewer user '{$user->email}' to admin role (update manually if needed)");
                }
            }
            $viewerRole->delete();
            $this->info("✓ Deleted 'viewer' role");
        }

        $this->info("\n✅ Role update completed!");
        
        // Verify warehouse manager user exists and update email if needed
        $warehouseRole = Role::where('slug', 'warehouse_manager')->first();
        if ($warehouseRole) {
            // Check for old email first
            $oldUser = User::where('email', 'qc@erp.com')->where('role_id', $warehouseRole->id)->first();
            if ($oldUser) {
                $oldUser->update([
                    'email' => 'warehouse@erp.com',
                    'name' => 'Warehouse Manager'
                ]);
                $this->info("✓ Updated user email from qc@erp.com to warehouse@erp.com");
            }
            
            $warehouseUser = User::where('email', 'warehouse@erp.com')->where('role_id', $warehouseRole->id)->first();
            if (!$warehouseUser) {
                $this->warn("\n⚠ Warehouse manager user not found. Run: php artisan db:seed");
            } else {
                $this->info("✓ Warehouse manager user exists: warehouse@erp.com (password: password)");
            }
        }

        return 0;
    }
}

