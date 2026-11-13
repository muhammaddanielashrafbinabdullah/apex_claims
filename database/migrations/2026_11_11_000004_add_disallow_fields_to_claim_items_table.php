<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds "disallow" fields to claim_items so an admin can reject single lines of a claim.
// Run with: php artisan migrate   (keeps all existing data; do NOT use migrate:fresh)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_items', function (Blueprint $table) {
            $table->boolean('disallowed')->default(false)->after('amount');
            $table->string('disallow_reason', 500)->nullable()->after('disallowed');
            $table->foreignId('disallowed_by')->nullable()->after('disallow_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('disallowed_at')->nullable()->after('disallowed_by');
        });
    }

    public function down(): void
    {
        Schema::table('claim_items', function (Blueprint $table) {
            $table->dropForeign(['disallowed_by']);
            $table->dropColumn(['disallowed', 'disallow_reason', 'disallowed_by', 'disallowed_at']);
        });
    }
};
