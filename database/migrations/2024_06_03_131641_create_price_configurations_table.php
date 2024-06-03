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
        Schema::create('price_configurations', function (Blueprint $table) {
            $table->id();
            $table->longText("Picture")->nullable();
            $table->longText("ProductId")->nullable();
            $table->longText("ProductName")->nullable();
            $table->decimal("Amount", 38, 30)->nullable();
            $table->longText("PricingType")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_configurations');
    }
};
