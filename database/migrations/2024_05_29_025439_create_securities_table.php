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
        Schema::create('securities', function (Blueprint $table) {
            $table->id();
            $table->longText("ipAddress")->nullable();
            $table->longText("country")->nullable();
            $table->longText("city")->nullable();
            $table->longText("device")->nullable();
            $table->longText("os")->nullable();
            $table->longText("urlPath")->nullable();
            $table->longText("action")->nullable();
            $table->longText("googlemap")->nullable();
            $table->longText("userId")->nullable();
            $table->longText("userName")->nullable();
            $table->longText("userPic")->nullable();
            $table->longText("SessionId")->nullable();
            $table->dateTime("lastLogin")->nullable();
            $table->dateTime("last_activity")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securities');
    }
};
