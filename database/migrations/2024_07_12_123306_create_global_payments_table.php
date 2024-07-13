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
        Schema::create('global_payments', function (Blueprint $table) {
            $table->id();
            $table->longText("tref")->nullable();
            $table->longText("ProductId")->nullable();
            $table->longText("Product")->nullable();
            $table->longText("Username")->nullable();
            $table->decimal("Amount")->nullable();
            $table->longText("SuccessApi")->nullable();
            $table->longText("CallbackURL")->nullable();
            $table->boolean("IsExecuted")->default(false);




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_payments');
    }
};
