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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->longText("TransactionId")->nullable();
            $table->longText("ProductId")->nullable();
            $table->longText("ProductName")->nullable();
            $table->longText("CustomerId")->nullable();
            $table->longText("CustomerName")->nullable();
            $table->longText("Created_By_Id")->nullable();
            $table->longText("Created_By_Name")->nullable();
            $table->longText("MeetingLink")->nullable();
            $table->dateTime("StartDate")->nullable();
            $table->dateTime("StartTime")->nullable();
            $table->longText("CustomerEmail")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
