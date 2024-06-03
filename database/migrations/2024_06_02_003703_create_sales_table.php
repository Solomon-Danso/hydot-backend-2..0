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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->longText("ProductId")->nullable();
            $table->longText("ProductName")->nullable();
            $table->longText("CustomerId")->nullable();
            $table->longText("CustomerName")->nullable();
            $table->longText("PaymentMethod")->nullable();
            $table->longText("PaymentReference")->nullable();
            $table->decimal("Amount", 38, 30)->nullable();
            $table->longText("Created_By_Id")->nullable();
            $table->longText("Created_By_Name")->nullable();
            $table->boolean("IsApproved")->default(false);
            $table->longText("Approved_By_Id")->nullable();
            $table->longText("Approved_By_Name")->nullable();
            $table ->longText("TransactionId") -> nullable();
            $table->longText("PricingType")->nullable();
            $table->integer("SubscriptionPeriodInDays")->nullable();
            $table->dateTime("StartDate")->nullable();
            $table->dateTime("SystemDate")->nullable();
            $table->dateTime("CurrentDate")->nullable();
            $table->dateTime("ExpireDate")->nullable();
           

         




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
