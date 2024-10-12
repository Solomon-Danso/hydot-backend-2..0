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
        Schema::table('client_apis', function (Blueprint $table) {
            $table->longText("apiHost")->nullable();
            $table->longText("apiKey")->nullable();
            $table->longText("apiSecret")->nullable();
            $table->longText("productId")->nullable();
            $table->longText("productName")->nullable();
            $table->longText("packageType")->nullable();
            $table->longText("softwareID")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_apis', function (Blueprint $table) {
            //
        });
    }
};
