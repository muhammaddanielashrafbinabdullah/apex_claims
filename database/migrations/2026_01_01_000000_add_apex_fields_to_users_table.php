<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds Apex fields to the users table that Laravel already created.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['staff', 'admin'])->default('staff')->index()->after('email');
            $table->string('staff_no')->nullable()->unique()->after('role');
            $table->string('designation')->nullable()->after('staff_no');
            $table->string('department')->nullable()->after('designation');
            $table->string('bank_account')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'staff_no', 'designation', 'department', 'bank_account']);
        });
    }
};
