<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Add-only. Existing claims and files are untouched:
//  - claim_attachments.item_key : which claim line a receipt belongs to (empty = "Other supporting documents").
//  - claim_items.breakdown      : the amounts the claimant added up, e.g. "1.10 + 2.40 + 3.00".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_attachments', function (Blueprint $table) {
            $table->string('item_key', 60)->nullable()->after('claim_id')->index();
        });

        Schema::table('claim_items', function (Blueprint $table) {
            $table->string('breakdown', 255)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('claim_attachments', function (Blueprint $table) {
            $table->dropIndex(['item_key']);
            $table->dropColumn('item_key');
        });

        Schema::table('claim_items', function (Blueprint $table) {
            $table->dropColumn('breakdown');
        });
    }
};
