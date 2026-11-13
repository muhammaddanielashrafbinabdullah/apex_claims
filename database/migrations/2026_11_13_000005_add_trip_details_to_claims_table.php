<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Add-only: three optional columns. Existing claims keep all their data (the new columns stay empty for them).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('trip_from', 255)->nullable()->after('vehicle_ownership');
            $table->string('trip_to', 255)->nullable()->after('trip_from');
            $table->text('trip_job')->nullable()->after('trip_to');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['trip_from', 'trip_to', 'trip_job']);
        });
    }
};
