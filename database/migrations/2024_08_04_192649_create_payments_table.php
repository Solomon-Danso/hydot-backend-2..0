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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->longText("type")->nullable();
            $table->longText("name")->nullable();
            $table->longText("account_number")->nullable();
            $table->longText("bank_code")->nullable();
            $table->longText("currency")->nullable();
            $table->longText("transfer_code")->nullable();
            $table->longText("recipient_code")->nullable();
            $table->longText("reference_code")->nullable();
            $table->decimal("amount")->nullable();
            $table->boolean("IsPayed")->default(false);



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
