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
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->longText("Picture1")->nullable();
            $table->longText("Picture2")->nullable();
            $table->longText("Picture3")->nullable();
            $table->longText("Picture4")->nullable();
            $table->longText("Picture5")->nullable();
            $table->longText("Picture6")->nullable();
            $table->longText("Section1")->nullable();
            $table->longText("Section2")->nullable();
            $table->longText("Section3")->nullable();
            $table->longText("Section4")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
