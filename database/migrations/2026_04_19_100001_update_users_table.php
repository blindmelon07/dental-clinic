<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('avatar')->nullable()->after('gender');
            $table->string('address')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('address');
            $table->boolean('is_active')->default(true)->after('city');
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'date_of_birth', 'gender', 'avatar', 'address', 'city', 'is_active', 'clinic_id']);
        });
    }
};
