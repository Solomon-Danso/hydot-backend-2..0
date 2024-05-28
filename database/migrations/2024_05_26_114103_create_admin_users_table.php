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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->longText("UserId")->nullable();
            $table->longText("Continent")->nullable();
            $table->longText("Country")->nullable();
            $table->longText("Picture")->nullable();
            $table->longText("Name")->nullable();
            $table->longText("Location")->nullable();
            $table->longText("Phone")->nullable();
            $table->longText("Email")->nullable();
            $table->longText("Password")->nullable();
            $table->longText("Role")->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
