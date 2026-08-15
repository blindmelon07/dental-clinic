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
        Schema::table('payments', function (Blueprint $table) {
            // Distinguishes payments the app auto-records by syncing a dental
            // record's partial_payment field from ones a staff member manually
            // recorded (installment collection, invoice "Record Payment"). Only
            // auto-synced payments are safe to adjust/delete when a later edit
            // lowers partial_payment — manual entries are a ledger of what was
            // actually collected and must never be rewritten automatically.
            $table->string('source')->default('manual')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
