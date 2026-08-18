<?php

namespace Database\Seeders;

use App\Models\ToothCondition;
use Illuminate\Database\Seeder;

class ToothConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            // Condition
            ['code' => 'D',  'label' => 'Decayed (Caries Indicated for Filling)', 'color' => '#ef4444', 'group' => 'Condition', 'sort_order' => 1],
            ['code' => 'M',  'label' => 'Missing due to Caries', 'color' => '#6b7280', 'group' => 'Condition', 'sort_order' => 2],
            ['code' => 'F',  'label' => 'Filled', 'color' => '#3b82f6', 'group' => 'Condition', 'sort_order' => 3],
            ['code' => 'I',  'label' => 'Caries Indicated for Extraction', 'color' => '#f97316', 'group' => 'Condition', 'sort_order' => 4],
            ['code' => 'RF', 'label' => 'Root Fragment', 'color' => '#92400e', 'group' => 'Condition', 'sort_order' => 5],
            ['code' => 'MO', 'label' => 'Missing due to Other Causes', 'color' => '#78716c', 'group' => 'Condition', 'sort_order' => 6],
            ['code' => 'Im', 'label' => 'Impacted Tooth', 'color' => '#a855f7', 'group' => 'Condition', 'sort_order' => 7],
            ['code' => 'AN', 'label' => 'Anodontia', 'color' => '#57534e', 'group' => 'Condition', 'sort_order' => 8],
            ['code' => 'PT', 'label' => 'Peg Tooth', 'color' => '#ca8a04', 'group' => 'Condition', 'sort_order' => 9],

            // Restoration & Prosthetics
            ['code' => 'J',   'label' => 'Jacket Crown', 'color' => '#f59e0b', 'group' => 'Restoration & Prosthetics', 'sort_order' => 1],
            ['code' => 'A',   'label' => 'Amalgam Filling', 'color' => '#64748b', 'group' => 'Restoration & Prosthetics', 'sort_order' => 2],
            ['code' => 'AB',  'label' => 'Abutment', 'color' => '#14b8a6', 'group' => 'Restoration & Prosthetics', 'sort_order' => 3],
            ['code' => 'P',   'label' => 'Pontic', 'color' => '#06b6d4', 'group' => 'Restoration & Prosthetics', 'sort_order' => 4],
            ['code' => 'In',  'label' => 'Inlay', 'color' => '#6366f1', 'group' => 'Restoration & Prosthetics', 'sort_order' => 5],
            ['code' => 'FX',  'label' => 'Fixed Cure Composite', 'color' => '#ec4899', 'group' => 'Restoration & Prosthetics', 'sort_order' => 6],
            ['code' => 'Rm',  'label' => 'Removable Denture', 'color' => '#84cc16', 'group' => 'Restoration & Prosthetics', 'sort_order' => 7],
            ['code' => 'RCT', 'label' => 'Root Canal Treatment (RCT)', 'color' => '#0ea5e9', 'group' => 'Restoration & Prosthetics', 'sort_order' => 8],

            // Surgery
            ['code' => 'X',  'label' => 'Extraction due to Caries', 'color' => '#dc2626', 'group' => 'Surgery', 'sort_order' => 1],
            ['code' => 'XO', 'label' => 'Extraction due to Other Causes', 'color' => '#7f1d1d', 'group' => 'Surgery', 'sort_order' => 2],
            ['code' => '✓',  'label' => 'Present Teeth', 'color' => '#22c55e', 'group' => 'Surgery', 'sort_order' => 3],
            ['code' => 'Cm', 'label' => 'Congenitally Missing', 'color' => '#8b5cf6', 'group' => 'Surgery', 'sort_order' => 4],
            ['code' => 'Sp', 'label' => 'Supernumerary', 'color' => '#d946ef', 'group' => 'Surgery', 'sort_order' => 5],
        ];

        foreach ($conditions as $condition) {
            ToothCondition::firstOrCreate(['code' => $condition['code']], $condition);
        }

        $this->command?->info('✅ Seeded ' . count($conditions) . ' tooth conditions.');
    }
}
