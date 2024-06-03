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
        Schema::create('client_apis', function (Blueprint $table) {
            $table->id();
            $table->longText("CompanyId")->nullable();
            $table->longText("CompanyName")->nullable();
            $table->longText("CompanyEmail")->nullable();
            $table->longText("CompanyPhone")->nullable();
            $table->longText("ApiServerURL")->nullable();
            $table->longText("ApiMediaURL")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_apis');
    }
};
