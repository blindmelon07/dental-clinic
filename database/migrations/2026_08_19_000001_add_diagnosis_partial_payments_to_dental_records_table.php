<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dental_records', function (Blueprint $table) {
            // Per-diagnosis-row partial payments, stored in the same order as the
            // `diagnosis` column's comma-separated service names. The `partial_payment`
            // column remains the source of truth for balance/invoice math — it's kept
            // in sync as the sum of these amounts whenever the form is saved.
            $table->json('diagnosis_partial_payments')->nullable()->after('diagnosis');
        });
    }

    public function down(): void
    {
        Schema::table('dental_records', function (Blueprint $table) {
            $table->dropColumn('diagnosis_partial_payments');
        });
    }
};
