<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('item_key');                 // e.g. meal_days, car_km (see config/claim_rates.php)
            $table->string('category');                 // e.g. "Meal Allowance"
            $table->string('description')->nullable();  // e.g. "> 100 km radius"
            $table->decimal('unit_input', 12, 2);       // days / nights / km / RM entered by staff
            $table->decimal('amount', 12, 2);           // server-calculated amount (RM)
            $table->timestamps();

            $table->unique(['claim_id', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_items');
    }
};
