<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(PermissionSeeder::class);

        // Create clinic
        $clinic = Clinic::firstOrCreate(
            ['slug' => 'gonzales-dental-main'],
            [
                'name'         => 'Gonzales Dental Clinic',
                'email'        => 'info@gonzalesdentalclinic.com',
                'phone'        => '(052) 742 1192 / (+63) 917-548-8934 / (+63) 918-633-1795',
                'address'      => '#28-1 Don Juan Estevez St. Guevara Subd.',
                'city'         => 'Legaspi City',
                'state'        => 'Albay',
                'country'      => 'Philippines',
                'description'  => 'Your trusted family dental clinic offering comprehensive oral health care.',
                'is_active'    => true,
                'business_hours' => [
                    'monday'    => ['open' => '08:00', 'close' => '17:00'],
                    'tuesday'   => ['open' => '08:00', 'close' => '17:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                    'thursday'  => ['open' => '08:00', 'close' => '17:00'],
                    'friday'    => ['open' => '08:00', 'close' => '17:00'],
                    'saturday'  => ['open' => '09:00', 'close' => '14:00'],
                ],
            ]
        );

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@dentcare.local'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'phone'     => '+63 900 000 0001',
                'is_active' => true,
                'clinic_id' => $clinic->id,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Receptionist
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@dentcare.local'],
            [
                'name'      => 'Maria Santos',
                'password'  => Hash::make('password'),
                'phone'     => '+63 900 000 0002',
                'is_active' => true,
                'clinic_id' => $clinic->id,
            ]
        );
        $receptionist->assignRole('receptionist');

        // Dentists
        $dentistUsers = [
            ['name' => 'Dr. Jose Reyes', 'email' => 'dr.reyes@dentcare.local', 'specialization' => 'General Dentistry', 'license' => 'PRC-12345'],
            ['name' => 'Dr. Ana Cruz', 'email' => 'dr.cruz@dentcare.local', 'specialization' => 'Orthodontics', 'license' => 'PRC-23456'],
            ['name' => 'Dr. Marco Lim', 'email' => 'dr.lim@dentcare.local', 'specialization' => 'Oral Surgery', 'license' => 'PRC-34567'],
        ];

        foreach ($dentistUsers as $d) {
            $dentistUser = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name'      => $d['name'],
                    'password'  => Hash::make('password'),
                    'is_active' => true,
                    'clinic_id' => $clinic->id,
                ]
            );
            $dentistUser->assignRole('dentist');

            $dentist = Dentist::firstOrCreate(
                ['user_id' => $dentistUser->id],
                [
                    'clinic_id'             => $clinic->id,
                    'license_number'        => $d['license'],
                    'specialization'        => $d['specialization'],
                    'consultation_fee'      => 500.00,
                    'consultation_duration' => 30,
                    'is_active'             => true,
                ]
            );

            // Create weekly schedule Mon-Sat
            for ($day = 1; $day <= 6; $day++) {
                DentistSchedule::firstOrCreate(
                    ['dentist_id' => $dentist->id, 'day_of_week' => $day],
                    [
                        'start_time'   => $day <= 5 ? '08:00' : '09:00',
                        'end_time'     => $day <= 5 ? '17:00' : '14:00',
                        'is_available' => true,
                    ]
                );
            }
        }

        // Service Categories
        $categories = [
            ['name' => 'Preventive', 'slug' => 'preventive', 'color' => '#10B981'],
            ['name' => 'Restorative', 'slug' => 'restorative', 'color' => '#3B82F6'],
            ['name' => 'Cosmetic', 'slug' => 'cosmetic', 'color' => '#8B5CF6'],
            ['name' => 'Orthodontics', 'slug' => 'orthodontics', 'color' => '#F59E0B'],
            ['name' => 'Oral Surgery', 'slug' => 'oral-surgery', 'color' => '#EF4444'],
        ];

        foreach ($categories as $i => $cat) {
            ServiceCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['sort_order' => $i + 1, 'is_active' => true])
            );
        }

        // Services
        $services = [
            ['category' => 'preventive', 'name' => 'Dental Check-up & Cleaning', 'price' => 800, 'duration' => 60],
            ['category' => 'preventive', 'name' => 'Fluoride Treatment', 'price' => 500, 'duration' => 30],
            ['category' => 'preventive', 'name' => 'Dental X-Ray', 'price' => 300, 'duration' => 15, 'xray' => true],
            ['category' => 'restorative', 'name' => 'Tooth Filling (Composite)', 'price' => 1500, 'duration' => 45],
            ['category' => 'restorative', 'name' => 'Tooth Filling (Amalgam)', 'price' => 1000, 'duration' => 45],
            ['category' => 'restorative', 'name' => 'Dental Crown', 'price' => 8000, 'duration' => 90],
            ['category' => 'restorative', 'name' => 'Root Canal Treatment', 'price' => 5000, 'duration' => 90],
            ['category' => 'cosmetic', 'name' => 'Teeth Whitening', 'price' => 3500, 'duration' => 60],
            ['category' => 'cosmetic', 'name' => 'Dental Veneers', 'price' => 12000, 'duration' => 120],
            ['category' => 'orthodontics', 'name' => 'Braces Consultation', 'price' => 500, 'duration' => 45],
            ['category' => 'orthodontics', 'name' => 'Metal Braces (Full)', 'price' => 35000, 'duration' => 120],
            ['category' => 'oral-surgery', 'name' => 'Tooth Extraction (Simple)', 'price' => 1200, 'duration' => 30],
            ['category' => 'oral-surgery', 'name' => 'Wisdom Tooth Extraction', 'price' => 4500, 'duration' => 60],
        ];

        foreach ($services as $i => $svc) {
            $category = ServiceCategory::where('slug', $svc['category'])->first();
            if (! $category) continue;

            Service::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($svc['name'])],
                [
                    'clinic_id'           => $clinic->id,
                    'service_category_id' => $category->id,
                    'name'                => $svc['name'],
                    'price'               => $svc['price'],
                    'duration_minutes'    => $svc['duration'],
                    'requires_xray'       => $svc['xray'] ?? false,
                    'is_active'           => true,
                    'sort_order'          => $i + 1,
                ]
            );
        }

        // Demo patient
        $patientUser = User::firstOrCreate(
            ['email' => 'patient@dentcare.local'],
            [
                'name'      => 'Juan dela Cruz',
                'password'  => Hash::make('password'),
                'phone'     => '+63 900 111 2222',
                'is_active' => true,
                'clinic_id' => $clinic->id,
            ]
        );
        $patientUser->assignRole('patient');

        Patient::firstOrCreate(
            ['user_id' => $patientUser->id],
            [
                'clinic_id'      => $clinic->id,
                'patient_number' => 'PT-20260419-0001',
                'first_name'     => 'Juan',
                'last_name'      => 'dela Cruz',
                'date_of_birth'  => '1990-06-15',
                'gender'         => 'male',
                'blood_type'     => 'O+',
                'phone'          => '+63 900 111 2222',
                'email'          => 'patient@dentcare.local',
                'address'        => '456 Sample Street',
                'city'           => 'Quezon City',
            ]
        );

        $this->command->info('✅ Gonzales Dental Clinic seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'admin@dentcare.local', 'password'],
                ['Receptionist', 'receptionist@dentcare.local', 'password'],
                ['Dentist', 'dr.reyes@dentcare.local', 'password'],
                ['Patient', 'patient@dentcare.local', 'password'],
            ]
        );
    }
}
