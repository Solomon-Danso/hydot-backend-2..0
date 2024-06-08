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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->longText("IpAddress")->nullabble();
            $table->longText("Country")->nullabble();
            $table->longText("City")->nullabble();
            $table->longText("Device")->nullabble();
            $table->longText("Os")->nullabble();
            $table->longText("googlemap")->nullabble();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
