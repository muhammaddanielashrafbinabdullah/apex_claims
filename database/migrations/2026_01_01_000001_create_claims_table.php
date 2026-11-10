<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Employee metadata snapshot (kept as submitted)
            $table->string('full_name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('staff_no');
            $table->string('bank_account');

            // Vehicle snapshot
            $table->string('vehicle_reg_no')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_capacity')->nullable();
            $table->string('vehicle_ownership')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->timestamp('submitted_at')->useCurrent();
            $table->unsignedTinyInteger('claim_month');
            $table->unsignedSmallInteger('claim_year');

            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->index(['claim_year', 'claim_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
