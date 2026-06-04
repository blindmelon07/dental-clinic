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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        // ── Clinic ────────────────────────────────────────────────────────────
        $clinic = Clinic::firstOrCreate(
            ['slug' => 'gonzales-dental-main'],
            [
                'name'           => 'Gonzales Dental Clinic',
                'email'          => 'info@gonzalesdentalclinic.com',
                'phone'          => '(052) 742 1192 / (+63) 917-548-8934 / (+63) 918-633-1795',
                'address'        => '#28-1 Don Juan Estevez St. Guevara Subd.',
                'city'           => 'Legaspi City',
                'state'          => 'Albay',
                'country'        => 'Philippines',
                'description'    => 'Your trusted family dental clinic offering comprehensive oral health care.',
                'is_active'      => true,
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

        // ── Users ─────────────────────────────────────────────────────────────
        $accounts = [
            [
                'role'     => 'super_admin',
                'name'     => 'Super Admin',
                'email'    => 'superadmin@gonzales.com',
                'password' => 'password',
                'phone'    => '+63 900 000 0001',
            ],
            [
                'role'     => 'admin',
                'name'     => 'Admin',
                'email'    => 'admin@gonzales.com',
                'password' => 'password',
                'phone'    => '+63 900 000 0002',
            ],
            [
                'role'     => 'receptionist',
                'name'     => 'Staff',
                'email'    => 'staff@gonzales.com',
                'password' => 'password',
                'phone'    => '+63 900 000 0003',
            ],
        ];

        foreach ($accounts as $a) {
            $user = User::firstOrCreate(
                ['email' => $a['email']],
                [
                    'name'      => $a['name'],
                    'password'  => Hash::make($a['password']),
                    'phone'     => $a['phone'],
                    'is_active' => true,
                    'clinic_id' => $clinic->id,
                ]
            );
            $user->assignRole($a['role']);
        }

        // ── Dr. Gonzales (dentist) ────────────────────────────────────────────
        $gonzalesUser = User::firstOrCreate(
            ['email' => 'dr.gonzales@gonzales.com'],
            [
                'name'      => 'Dr. Famiran Ghandali - Gonzales',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'clinic_id' => $clinic->id,
            ]
        );
        $gonzalesUser->assignRole('dentist');

        $gonzalesDentist = Dentist::firstOrCreate(
            ['user_id' => $gonzalesUser->id],
            [
                'clinic_id'             => $clinic->id,
                'license_number'        => '0047299',
                'specialization'        => 'General Dentistry, Orthodontics, Implant Dentistry',
                'consultation_fee'      => 0,
                'consultation_duration' => 30,
                'is_active'             => true,
            ]
        );

        for ($day = 1; $day <= 6; $day++) {
            DentistSchedule::firstOrCreate(
                ['dentist_id' => $gonzalesDentist->id, 'day_of_week' => $day],
                [
                    'start_time'   => $day <= 5 ? '08:00' : '09:00',
                    'end_time'     => $day <= 5 ? '17:00' : '14:00',
                    'is_available' => true,
                ]
            );
        }

        // ── Service Categories & Services ─────────────────────────────────────
        $catalog = [
            [
                'name'  => 'Panoramic',
                'slug'  => 'panoramic',
                'color' => '#3B82F6',
                'services' => [
                    'Standard',
                    'Sinus',
                    'TMJ (Open and Close)',
                    'Bitewing',
                ],
            ],
            [
                'name'  => 'Cephalometric',
                'slug'  => 'cephalometric',
                'color' => '#10B981',
                'services' => [
                    'Posterio / Anterior (PA)',
                    'Anterior / Posterior (AP)',
                    'Latero / Lateral (LL)',
                    'Waters View',
                    'SMV',
                    'Carpus',
                ],
            ],
            [
                'name'  => '3D CBCT',
                'slug'  => '3d-cbct',
                'color' => '#8B5CF6',
                'services' => [
                    '5x5 (Endo)',
                    '12x9.5 (Standard)',
                ],
            ],
        ];

        foreach ($catalog as $catOrder => $cat) {
            $category = ServiceCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'       => $cat['name'],
                    'color'      => $cat['color'],
                    'sort_order' => $catOrder + 1,
                    'is_active'  => true,
                ]
            );

            foreach ($cat['services'] as $svcOrder => $svcName) {
                Service::firstOrCreate(
                    ['slug' => Str::slug($cat['slug'] . '-' . $svcName)],
                    [
                        'clinic_id'           => $clinic->id,
                        'service_category_id' => $category->id,
                        'name'                => $svcName,
                        'price'               => 0,
                        'duration_minutes'    => 15,
                        'requires_xray'       => true,
                        'is_active'           => true,
                        'sort_order'          => $svcOrder + 1,
                    ]
                );
            }
        }

        // ── Demo Patient ──────────────────────────────────────────────────────
        $patientUser = User::firstOrCreate(
            ['email' => 'patient@gonzales.com'],
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
                'email'          => 'patient@gonzales.com',
                'address'        => '456 Sample Street',
                'city'           => 'Legaspi City',
            ]
        );

        // ── Summary ───────────────────────────────────────────────────────────
        $this->command->info('✅ Gonzales Dental Clinic seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin',  'superadmin@gonzales.com',  'password'],
                ['Admin',        'admin@gonzales.com',        'password'],
                ['Staff',        'staff@gonzales.com',        'password'],
                ['Dentist',      'dr.gonzales@gonzales.com',  'password'],
                ['Patient',      'patient@gonzales.com',      'password'],
            ]
        );
    }
}
