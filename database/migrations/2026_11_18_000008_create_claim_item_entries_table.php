<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row per receipt (amount + its own file(s)). Adds tables/columns only: nothing existing is changed or deleted.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_item_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_item_id')->constrained('claim_items')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('claim_attachments', function (Blueprint $table) {
            $table->foreignId('entry_id')->nullable()->after('item_key')
                ->constrained('claim_item_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('claim_attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entry_id');
        });
        Schema::dropIfExists('claim_item_entries');
    }
};
