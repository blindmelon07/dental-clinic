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
            $table->foreignId('medicine_form_id')->nullable()->after('form')
                ->constrained('medicine_forms')->nullOnDelete();
        });

        $formIdsBySlug = DB::table('medicine_forms')->pluck('id', 'slug');

        foreach ($formIdsBySlug as $slug => $formId) {
            DB::table('medicines')->where('form', $slug)->update(['medicine_form_id' => $formId]);
        }

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('form');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('form')->default('tablet')->after('medicine_category_id');
        });

        DB::table('medicines')
            ->join('medicine_forms', 'medicines.medicine_form_id', '=', 'medicine_forms.id')
            ->update(['medicines.form' => DB::raw('medicine_forms.slug')]);

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_form_id');
        });
    }
};
