<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('medicine_category_id')->nullable()->after('category')
                ->constrained('medicine_categories')->nullOnDelete();
        });

        $categoryIdsBySlug = DB::table('medicine_categories')->pluck('id', 'slug');

        foreach ($categoryIdsBySlug as $slug => $categoryId) {
            DB::table('medicines')->where('category', $slug)->update(['medicine_category_id' => $categoryId]);
        }

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('category')->nullable()->after('brand');
        });

        DB::table('medicines')
            ->join('medicine_categories', 'medicines.medicine_category_id', '=', 'medicine_categories.id')
            ->update(['medicines.category' => DB::raw('medicine_categories.slug')]);

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_category_id');
        });
    }
};
