<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $defaults = [
        ['slug' => 'antibiotic', 'name' => 'Antibiotic', 'color' => '#ef4444', 'sort_order' => 1],
        ['slug' => 'analgesic', 'name' => 'Analgesic / Pain Reliever', 'color' => '#f59e0b', 'sort_order' => 2],
        ['slug' => 'anti_inflammatory', 'name' => 'Anti-Inflammatory', 'color' => '#f97316', 'sort_order' => 3],
        ['slug' => 'anesthetic', 'name' => 'Anesthetic', 'color' => '#8b5cf6', 'sort_order' => 4],
        ['slug' => 'antiseptic', 'name' => 'Antiseptic', 'color' => '#06b6d4', 'sort_order' => 5],
        ['slug' => 'antifungal', 'name' => 'Antifungal', 'color' => '#10b981', 'sort_order' => 6],
        ['slug' => 'antihistamine', 'name' => 'Antihistamine', 'color' => '#ec4899', 'sort_order' => 7],
        ['slug' => 'vitamin', 'name' => 'Vitamin / Supplement', 'color' => '#22c55e', 'sort_order' => 8],
        ['slug' => 'other', 'name' => 'Other', 'color' => '#6b7280', 'sort_order' => 9],
    ];

    public function up(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#3B82F6');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('medicine_categories')->insert(array_map(
            fn (array $category) => [...$category, 'created_at' => $now, 'updated_at' => $now],
            $this->defaults
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_categories');
    }
};
