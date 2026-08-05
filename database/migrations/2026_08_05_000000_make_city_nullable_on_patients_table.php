<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `city` was never actually required by either patient-facing form (public
     * self-registration validates it as nullable, and the admin Create Patient
     * form doesn't mark it required either) — but the column itself was still
     * NOT NULL, so approving a registration left blank crashed with a raw SQL
     * "Column 'city' cannot be null" error. Matches the sibling `province`
     * column, which got the same nullable() fix in an earlier migration.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('city')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('city')->nullable(false)->change();
        });
    }
};
