<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $clinic = Clinic::where('slug', 'gonzales-dental-main')->first();

        // ── 1. Site Settings ─────────────────────────────────────────────────
        SiteSetting::instance()->update([
            'clinic_name'  => 'Gonzales Dental Clinic',
            'tagline'      => 'Your Trusted Dental Care Partner',
            'address'      => '#28-1 Don Juan Estevez St. Guevara Subd.',
            'city'         => 'Legaspi City',
            'phone'        => '(052) 742-1192',
            'email'        => 'info@gonzalesdentalclinic.com',
            'facebook_url' => 'https://facebook.com/gonzalesdentalclinic',
            'footer_text'  => '© ' . date('Y') . ' Gonzales Dental Clinic. All rights reserved.',

            // Hero
            'hero_heading'     => 'Healthy Smiles',
            'hero_subheading'  => 'Start Here',
            'hero_description' => 'Your Trusted Dental Care Partner. From routine cleanings to advanced cosmetic procedures, our experienced team is dedicated to giving you a smile you\'ll love.',

            // Stats
            'stat_years'        => '15+',
            'stat_patients'     => '10k+',
            'stat_satisfaction' => '98%',
            'stat_emergency'    => '24/7',

            // Hours
            'hours_weekday'  => '8:00 AM – 5:00 PM',
            'hours_saturday' => '9:00 AM – 2:00 PM',
            'hours_sunday'   => 'Closed',

            // Home testimonial
            'testimonial_quote'  => 'The entire team made me feel completely at ease. They explained everything clearly and the office is spotless and modern. I actually look forward to my checkups now!',
            'testimonial_author' => 'Maria Santos',
            'testimonial_since'  => 'Patient since 2021',

            // About story
            'about_story_heading' => 'Care With Compassion',
            'about_story_body'    => 'Gonzales Dental Clinic was founded with a simple goal: to make quality dental care approachable, comfortable, and stress-free for every patient who walks through our doors. What started as a small practice has grown into a trusted community clinic, serving thousands of families across Legaspi City and the surrounding areas of Albay.' . "\n\n" . 'Today, our team combines years of clinical experience with modern technology and a genuine commitment to compassionate care — because we believe a healthy smile starts with feeling at ease.',

            // Milestones
            'milestone_1_title' => 'Our Clinic Opens',
            'milestone_1_body'  => 'We opened our doors on Don Juan Estevez St. with a small but passionate team and a big mission — to provide friendly, honest dental care to the families of Legaspi City.',
            'milestone_2_title' => 'Expanding Our Services',
            'milestone_2_body'  => 'As our patient family grew, so did our services — adding cosmetic dentistry, orthodontics, implants, and advanced imaging to serve every stage of oral health.',
            'milestone_3_title' => 'Going Digital',
            'milestone_3_body'  => 'We introduced digital X-rays, CBCT imaging, and our online patient portal to make care more accurate, convenient, and accessible than ever before.',
            'milestone_4_title' => 'Serving You Today',
            'milestone_4_body'  => 'Today, we continue to grow — guided by the same values we started with: compassion, quality, and trust. We look forward to serving generations of smiles to come.',

            // About testimonial
            'about_testimonial_quote'  => 'From the moment you walk in, you can tell this clinic genuinely cares. The staff is welcoming, the office is immaculate, and every visit feels personal. Dr. Gonzales truly takes her time with each patient.',
            'about_testimonial_author' => 'James Rivera',
            'about_testimonial_since'  => 'Patient since 2019',
        ]);

        // ── 2. Update existing dentist (Dr. Famiran) with a real fee ─────────
        Dentist::find(1)?->update(['consultation_fee' => 400]);

        // ── 3. Update existing services with real prices ──────────────────────
        $prices = [
            'Standard'             => 350,
            'Sinus'                => 450,
            'TMJ (Open and Close)' => 600,
            'Bitewing'             => 250,
            'Posterio / Anterior (PA)' => 400,
            'Anterior / Posterior (AP)' => 400,
            'Latero / Lateral (LL)'    => 400,
            'Waters View'          => 450,
            'SMV'                  => 450,
            'Carpus'               => 350,
            '5x5 (Endo)'           => 3500,
            '12x9.5 (Standard)'    => 5000,
        ];

        foreach ($prices as $name => $price) {
            Service::where('name', $name)->update(['price' => $price]);
        }

        // ── 4. New service categories with realistic prices ───────────────────
        $newCatalog = [
            [
                'name'        => 'General Dentistry',
                'slug'        => 'general-dentistry',
                'description' => 'Routine dental care to keep your teeth and gums healthy.',
                'color'       => '#0EA5E9',
                'sort_order'  => 4,
                'services'    => [
                    ['name' => 'Dental Consultation',    'price' => 300,  'duration' => 30,  'description' => 'Comprehensive oral examination and treatment planning.'],
                    ['name' => 'Teeth Cleaning (Prophylaxis)', 'price' => 800, 'duration' => 45, 'description' => 'Professional removal of plaque and tartar to prevent gum disease.'],
                    ['name' => 'Tooth Extraction (Simple)', 'price' => 500, 'duration' => 30, 'description' => 'Safe removal of a damaged or decayed tooth.'],
                    ['name' => 'Tooth Extraction (Surgical)', 'price' => 1500, 'duration' => 60, 'description' => 'Surgical removal of impacted or complex teeth.'],
                    ['name' => 'Dental Filling (Composite)', 'price' => 1200, 'duration' => 45, 'description' => 'Tooth-colored resin filling that blends naturally with your teeth.'],
                    ['name' => 'Dental Filling (Amalgam)', 'price' => 800, 'duration' => 45, 'description' => 'Durable silver filling for back teeth with heavy chewing load.'],
                ],
            ],
            [
                'name'        => 'Cosmetic Dentistry',
                'slug'        => 'cosmetic-dentistry',
                'description' => 'Smile enhancement treatments for a brighter, more confident you.',
                'color'       => '#EC4899',
                'sort_order'  => 5,
                'services'    => [
                    ['name' => 'Teeth Whitening',        'price' => 5000,  'duration' => 60,  'description' => 'Professional in-office whitening for a noticeably brighter smile in one session.'],
                    ['name' => 'Dental Veneers (per tooth)', 'price' => 8000, 'duration' => 90, 'description' => 'Thin porcelain shells bonded to the front of teeth for a flawless appearance.'],
                    ['name' => 'Dental Bonding',         'price' => 2500,  'duration' => 60,  'description' => 'Tooth-colored resin applied to repair chips, cracks, or gaps.'],
                    ['name' => 'Smile Makeover',         'price' => 25000, 'duration' => 120, 'description' => 'Comprehensive cosmetic treatment plan tailored to your unique smile goals.'],
                ],
            ],
            [
                'name'        => 'Orthodontics',
                'slug'        => 'orthodontics',
                'description' => 'Teeth straightening solutions for a perfectly aligned smile.',
                'color'       => '#8B5CF6',
                'sort_order'  => 6,
                'services'    => [
                    ['name' => 'Braces (Metal)',          'price' => 35000, 'duration' => 60,  'description' => 'Traditional metal braces for effective teeth alignment at an affordable cost.'],
                    ['name' => 'Braces (Ceramic)',        'price' => 45000, 'duration' => 60,  'description' => 'Tooth-colored ceramic braces — less visible than metal, equally effective.'],
                    ['name' => 'Orthodontic Retainer',   'price' => 3500,  'duration' => 30,  'description' => 'Custom retainer to maintain teeth position after braces removal.'],
                    ['name' => 'Clear Aligners',         'price' => 60000, 'duration' => 45,  'description' => 'Nearly invisible removable aligners for a discreet straightening experience.'],
                ],
            ],
            [
                'name'        => 'Restorative Dentistry',
                'slug'        => 'restorative-dentistry',
                'description' => 'Restoring damaged or missing teeth to full function and aesthetics.',
                'color'       => '#F59E0B',
                'sort_order'  => 7,
                'services'    => [
                    ['name' => 'Dental Crown (Porcelain)', 'price' => 8000, 'duration' => 90, 'description' => 'A tooth-shaped cap placed over a damaged tooth to restore shape, size, and strength.'],
                    ['name' => 'Dental Bridge',           'price' => 15000, 'duration' => 90, 'description' => 'Fixed prosthetic to replace one or more missing teeth anchored to adjacent teeth.'],
                    ['name' => 'Dental Implant',          'price' => 45000, 'duration' => 120, 'description' => 'A titanium post surgically placed into the jaw to serve as an artificial tooth root.'],
                    ['name' => 'Dentures (Full)',         'price' => 20000, 'duration' => 60,  'description' => 'Complete removable dentures for patients missing all teeth in an arch.'],
                    ['name' => 'Dentures (Partial)',      'price' => 12000, 'duration' => 60,  'description' => 'Removable partial dentures for patients missing several teeth.'],
                    ['name' => 'Root Canal Treatment',   'price' => 6500,  'duration' => 90,  'description' => 'Removal of infected pulp tissue to save a severely damaged tooth.'],
                ],
            ],
        ];

        foreach ($newCatalog as $cat) {
            $category = ServiceCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'],
                    'color'       => $cat['color'],
                    'sort_order'  => $cat['sort_order'],
                    'is_active'   => true,
                ]
            );

            foreach ($cat['services'] as $svcOrder => $svc) {
                Service::firstOrCreate(
                    ['slug' => Str::slug($cat['slug'] . '-' . $svc['name'])],
                    [
                        'clinic_id'           => $clinic?->id,
                        'service_category_id' => $category->id,
                        'name'                => $svc['name'],
                        'description'         => $svc['description'],
                        'price'               => $svc['price'],
                        'duration_minutes'    => $svc['duration'],
                        'requires_xray'       => false,
                        'is_active'           => true,
                        'sort_order'          => $svcOrder + 1,
                    ]
                );
            }
        }

        // ── 5. Additional dentists ────────────────────────────────────────────
        $dentistData = [
            [
                'name'          => 'Dr. Ana Reyes',
                'email'         => 'dr.reyes@gonzales.com',
                'specialization'=> 'Cosmetic Dentistry, Veneers & Whitening',
                'bio'           => 'Dr. Reyes specializes in smile transformations with over 10 years of cosmetic dentistry experience. She is known for her artistic eye and gentle approach.',
                'fee'           => 500,
                'license'       => '0052341',
            ],
            [
                'name'          => 'Dr. Carlo Mendoza',
                'email'         => 'dr.mendoza@gonzales.com',
                'specialization'=> 'Orthodontics & Clear Aligners',
                'bio'           => 'Dr. Mendoza is a certified orthodontist who has helped hundreds of patients achieve perfectly aligned smiles using both traditional braces and modern clear aligners.',
                'fee'           => 600,
                'license'       => '0061892',
            ],
        ];

        foreach ($dentistData as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name'      => $d['name'],
                    'password'  => Hash::make('password'),
                    'is_active' => true,
                    'clinic_id' => $clinic?->id,
                ]
            );
            $user->assignRole('dentist');

            $dentist = Dentist::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'clinic_id'             => $clinic?->id,
                    'license_number'        => $d['license'],
                    'specialization'        => $d['specialization'],
                    'bio'                   => $d['bio'],
                    'consultation_fee'      => $d['fee'],
                    'consultation_duration' => 30,
                    'is_active'             => true,
                ]
            );

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

        // ── Done ─────────────────────────────────────────────────────────────
        $this->command->info('✅ Sample data seeded!');
        $this->command->table(
            ['What was seeded', 'Count'],
            [
                ['Site Settings updated',        '1'],
                ['Existing service prices fixed', count($prices)],
                ['New service categories',        count($newCatalog)],
                ['New services',                  collect($newCatalog)->sum(fn($c) => count($c['services']))],
                ['New dentists added',            count($dentistData)],
            ]
        );
    }
}
