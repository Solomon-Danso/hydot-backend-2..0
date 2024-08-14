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
        Schema::create('on_boardings', function (Blueprint $table) {
            $table->id();

            $table ->longText("TransactionId") -> nullable();

            $table->longText("ProductId")->nullable();
            $table->longText("ProductName")->nullable();
            $table->longText("CustomerId")->nullable();
            $table->longText("CustomerName")->nullable();
            $table->longText("CustomerEmail")->nullable();
            $table->longText("PaymentMethod")->nullable();
            $table->longText("PaymentReference")->nullable();
            $table->decimal("Amount", 38, 30)->nullable();
            $table->longText("Created_By_Id")->nullable();
            $table->longText("Created_By_Name")->nullable();
            $table->longText("PricingType")->nullable();

            $table->boolean("FirstMeeting")->default(false);
            $table->boolean("Step1Completed")->default(false);
            $table->longText("Step1AdminId")->nullable();

            $table->boolean("MOUAgreement")->default(false);
            $table->boolean("DomainAndHosting")->default(false);
            $table->boolean("PaymentCompleted")->default(false);
            $table->boolean("Step2Completed")->default(false);
            $table->longText("Step2AdminId")->nullable();

            $table->boolean("SoftwareUpload")->default(false);
            $table->boolean("ThirdPartyServices")->default(false);
            $table->boolean("Testing")->default(false);
            $table->boolean("Step3Completed")->default(false);
            $table->longText("Step3AdminId")->nullable();

            $table->boolean("UserManual")->default(false);
            $table->boolean("MOUSignature")->default(false);
            $table->boolean("Step4Completed")->default(false);
            $table->longText("Step4AdminId")->nullable();






            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('on_boardings');
    }
};
