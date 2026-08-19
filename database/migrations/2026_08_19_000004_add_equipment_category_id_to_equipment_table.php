<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('equipment_category_id')->nullable()->after('category')
                ->constrained('equipment_categories')->nullOnDelete();
        });

        // Match each row's free-text `category` (as saved by the old hardcoded
        // select) onto the matching seeded category by name, case-insensitively.
        $categoryIdsByName = DB::table('equipment_categories')->get(['id', 'name'])
            ->mapWithKeys(fn ($row) => [strtolower($row->name) => $row->id]);

        foreach ($categoryIdsByName as $name => $categoryId) {
            DB::table('equipment')->whereRaw('LOWER(category) = ?', [$name])->update(['equipment_category_id' => $categoryId]);
        }

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        DB::table('equipment')
            ->join('equipment_categories', 'equipment.equipment_category_id', '=', 'equipment_categories.id')
            ->update(['equipment.category' => DB::raw('equipment_categories.name')]);

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_category_id');
        });
    }
};
