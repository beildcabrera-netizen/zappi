<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ventas.anular',
            'productos.create',
            'productos.edit',
            'productos.delete',
            'compras.create',
            'compras.edit',
            'usuarios.*',
            'reportes.*',
            'configuracion.*',
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin->syncPermissions($createdPermissions);

        $vendedor = Role::firstOrCreate(['name' => 'vendedor', 'guard_name' => 'web']);
        $vendedor->syncPermissions([
            Permission::where('name', 'ventas.anular')->first(),
            Permission::where('name', 'productos.create')->first(),
            Permission::where('name', 'productos.edit')->first(),
            Permission::where('name', 'compras.create')->first(),
        ]);

        $cajero = Role::firstOrCreate(['name' => 'cajero', 'guard_name' => 'web']);
        $cajero->syncPermissions([
            Permission::where('name', 'ventas.anular')->first(),
            Permission::where('name', 'productos.edit')->first(),
        ]);
    }
}
