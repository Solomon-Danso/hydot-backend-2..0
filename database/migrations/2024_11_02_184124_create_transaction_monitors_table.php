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
        Schema::create('transaction_monitors', function (Blueprint $table) {
            $table->id();
            $table->longText("TransactionType")->nullable();
            $table->longText("Narration")->nullable();
            $table->decimal("Credit")->default(0);
            $table->decimal("Debit")->default(0);
            $table->decimal("Balance")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_monitors');
    }
};
