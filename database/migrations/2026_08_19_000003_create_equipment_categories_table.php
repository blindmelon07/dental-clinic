<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Seeds the same categories the Equipment form previously hardcoded, so
    // existing 'category' text values on equipment rows still match a slug
    // when the next migration moves them onto this table.
    private array $defaults = [
        ['slug' => 'diagnostic', 'name' => 'Diagnostic', 'color' => '#3b82f6', 'sort_order' => 1],
        ['slug' => 'surgical', 'name' => 'Surgical', 'color' => '#ef4444', 'sort_order' => 2],
        ['slug' => 'sterilization', 'name' => 'Sterilization', 'color' => '#06b6d4', 'sort_order' => 3],
        ['slug' => 'imaging', 'name' => 'Imaging', 'color' => '#8b5cf6', 'sort_order' => 4],
        ['slug' => 'restorative', 'name' => 'Restorative', 'color' => '#f59e0b', 'sort_order' => 5],
        ['slug' => 'orthodontic', 'name' => 'Orthodontic', 'color' => '#ec4899', 'sort_order' => 6],
        ['slug' => 'furniture', 'name' => 'Furniture', 'color' => '#22c55e', 'sort_order' => 7],
        ['slug' => 'other', 'name' => 'Other', 'color' => '#6b7280', 'sort_order' => 8],
    ];

    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#3B82F6');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('equipment_categories')->insert(array_map(
            fn (array $category) => [...$category, 'created_at' => $now, 'updated_at' => $now],
            $this->defaults
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
