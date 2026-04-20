<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    // Permission matrix:
    // super_admin — everything
    // admin       — everything except user management (no delete users)
    // receptionist— patients (view/create/edit), appointments (all), invoices (all), services (view)
    // dentist     — patients (view), appointments (view/edit own), dental records (all), services (view/edit)

    private array $permissions = [
        // Patients
        'view_any_patient',
        'view_patient',
        'create_patient',
        'update_patient',
        'delete_patient',

        // Appointments
        'view_any_appointment',
        'view_appointment',
        'create_appointment',
        'update_appointment',
        'delete_appointment',

        // Dental Records
        'view_any_dental_record',
        'view_dental_record',
        'create_dental_record',
        'update_dental_record',
        'delete_dental_record',

        // Invoices
        'view_any_invoice',
        'view_invoice',
        'create_invoice',
        'update_invoice',
        'delete_invoice',

        // Services
        'view_any_service',
        'view_service',
        'create_service',
        'update_service',
        'delete_service',

        // Dentists
        'view_any_dentist',
        'view_dentist',
        'create_dentist',
        'update_dentist',
        'delete_dentist',

        // Prescriptions
        'view_any_prescription',
        'view_prescription',
        'create_prescription',
        'update_prescription',
        'delete_prescription',

        // Users (admin management)
        'view_any_user',
        'view_user',
        'create_user',
        'update_user',
        'delete_user',
    ];

    private array $rolePermissions = [
        'super_admin' => '*', // gets all permissions

        'admin' => [
            'view_any_patient', 'view_patient', 'create_patient', 'update_patient', 'delete_patient',
            'view_any_appointment', 'view_appointment', 'create_appointment', 'update_appointment', 'delete_appointment',
            'view_any_dental_record', 'view_dental_record', 'create_dental_record', 'update_dental_record', 'delete_dental_record',
            'view_any_prescription', 'view_prescription', 'create_prescription', 'update_prescription', 'delete_prescription',
            'view_any_invoice', 'view_invoice', 'create_invoice', 'update_invoice', 'delete_invoice',
            'view_any_service', 'view_service', 'create_service', 'update_service', 'delete_service',
            'view_any_dentist', 'view_dentist', 'create_dentist', 'update_dentist', 'delete_dentist',
            'view_any_user', 'view_user', 'create_user', 'update_user',
        ],

        'receptionist' => [
            'view_any_patient', 'view_patient', 'create_patient', 'update_patient',
            'view_any_appointment', 'view_appointment', 'create_appointment', 'update_appointment', 'delete_appointment',
            'view_any_prescription', 'view_prescription',
            'view_any_invoice', 'view_invoice', 'create_invoice', 'update_invoice',
            'view_any_service', 'view_service',
            'view_any_dentist', 'view_dentist',
        ],

        'dentist' => [
            'view_any_patient', 'view_patient',
            'view_any_appointment', 'view_appointment', 'update_appointment',
            'view_any_dental_record', 'view_dental_record', 'create_dental_record', 'update_dental_record',
            'view_any_prescription', 'view_prescription', 'create_prescription', 'update_prescription',
            'view_any_service', 'view_service', 'update_service',
            'view_any_dentist', 'view_dentist',
        ],

        'patient' => [],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach ($this->rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === '*') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }

            $this->command->info("✅ Role [{$roleName}] assigned " . ($perms === '*' ? 'all' : count($perms)) . " permissions.");
        }
    }
}
