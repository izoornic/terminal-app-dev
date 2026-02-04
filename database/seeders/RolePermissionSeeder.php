<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define permissions
        $permissions = [
            'view-dashboard',
            'manage-users',
            'manage-terminals',
            'manage-services',
            'view-reports',
            'manage-licenses',
            'manage-distributors',
            'manage-bankomati',
            'parts.types.manage',
            'parts.stock.view.all',
            'parts.stock.view.own',
            'parts.movements.create',
            'parts.movements.view',
            'parts.transfer.execute',
            'parts.reservation.manage',
            'parts.reservation.view',
            'parts.inventory.perform',
            'parts.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles based on pozicija_tips
        $this->assignPermissionsToRole('Admin', [
            'view-dashboard',
            'manage-users',
            'manage-terminals',
            'manage-services',
            'view-reports',
            'manage-licenses',
            'manage-distributors',
            'manage-bankomati',
            'parts.types.manage',
            'parts.stock.view.all',
            'parts.movements.create',
            'parts.movements.view',
            'parts.transfer.execute',
            'parts.reservation.manage',
            'parts.reservation.view',
            'parts.inventory.perform',
            'parts.reports.view',
        ]);

        $this->assignPermissionsToRole('Call centar', [
            'view-dashboard',
            'view-reports',
            'manage-services',
        ]);

        $this->assignPermissionsToRole('Šef servisa', [
            'view-dashboard',
            'manage-services',
            'view-reports',
            'parts.stock.view.all',
            'parts.movements.create',
            'parts.movements.view',
            'parts.transfer.execute',
            'parts.reservation.manage',
            'parts.reservation.view',
            'parts.inventory.perform',
            'parts.reports.view',
        ]);

        $this->assignPermissionsToRole('Serviser', [
            'view-dashboard',
            'manage-services',
            'parts.stock.view.own',
            'parts.movements.view',
            'parts.reservation.manage',
            'parts.reservation.view',
        ]);

        $this->assignPermissionsToRole('Menadžer licenci', [
            'view-dashboard',
            'manage-licenses',
            'view-reports',
        ]);

        $this->assignPermissionsToRole('Distributer', [
            'view-dashboard',
            'manage-distributors',
        ]);

        $this->assignPermissionsToRole('Admin bankomata', [
            'view-dashboard',
            'manage-bankomati',
        ]);

        $this->assignPermissionsToRole('Šef servisa bankomata', [
            'view-dashboard',
            'manage-bankomati',
        ]);

        $this->assignPermissionsToRole('Serviser bankomata', [
            'view-dashboard',
            'manage-bankomati',
        ]);
    }
    
    private function assignPermissionsToRole($roleName, $permissions)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $role->syncPermissions($permissions);
        }
    }
}
