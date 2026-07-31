<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $defaults = [
        ['slug' => 'tablet', 'name' => 'Tablet'],
        ['slug' => 'capsule', 'name' => 'Capsule'],
        ['slug' => 'syrup', 'name' => 'Syrup / Liquid'],
        ['slug' => 'ointment', 'name' => 'Ointment / Cream'],
        ['slug' => 'drops', 'name' => 'Drops'],
        ['slug' => 'injection', 'name' => 'Injection'],
        ['slug' => 'other', 'name' => 'Other'],
    ];

    public function up(): void
    {
        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('medicine_forms')->insert(array_map(
            fn (array $form) => [...$form, 'created_at' => $now, 'updated_at' => $now],
            $this->defaults
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_forms');
    }
};
