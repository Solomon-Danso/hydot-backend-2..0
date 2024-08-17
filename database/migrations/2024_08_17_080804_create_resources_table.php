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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->longText("ResourceId")->nullable();
            $table->longText("Category")->nullable();
            $table->longText("ResourceType")->nullable();
            $table->longText("Title")->nullable();
            $table->longText("UserId")->nullable();
            $table->longText("Name")->nullable();
            $table->longText("Email")->nullable();
            $table->longText("Resource")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
