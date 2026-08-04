<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_cleaning')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the types that used to live in the App\Enums\AppointmentType enum,
        // and rewrite existing appointments.type values (old enum ->value, e.g.
        // "follow_up") to match the new human-readable names (e.g. "Follow-up").
        $types = [
            ['old' => 'consultation', 'name' => 'Consultation', 'is_cleaning' => false, 'sort_order' => 1],
            ['old' => 'follow_up', 'name' => 'Follow-up', 'is_cleaning' => false, 'sort_order' => 2],
            ['old' => 'emergency', 'name' => 'Emergency', 'is_cleaning' => false, 'sort_order' => 3],
            ['old' => 'cleaning', 'name' => 'Cleaning', 'is_cleaning' => true, 'sort_order' => 4],
            ['old' => 'procedure', 'name' => 'Procedure', 'is_cleaning' => false, 'sort_order' => 5],
            ['old' => 'xray', 'name' => 'X-Ray', 'is_cleaning' => false, 'sort_order' => 6],
        ];

        foreach ($types as $type) {
            DB::table('appointment_types')->insert([
                'name'        => $type['name'],
                'is_cleaning' => $type['is_cleaning'],
                'is_active'   => true,
                'sort_order'  => $type['sort_order'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('appointments')->where('type', $type['old'])->update(['type' => $type['name']]);
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('type')->default('Consultation')->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('type')->default('consultation')->change();
        });

        Schema::dropIfExists('appointment_types');
    }
};
