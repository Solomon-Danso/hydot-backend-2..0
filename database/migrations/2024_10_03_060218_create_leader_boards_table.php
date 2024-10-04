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
        Schema::create('leader_boards', function (Blueprint $table) {
            $table->id();
            $table->longText("FullName")->nullable();
            $table->longText("UserId")->nullable();
            $table->decimal('Sales', 65, 30)->nullable();
            $table->bigInteger('FirstCount')->nullable();
            $table->bigInteger('SecondCount')->nullable();
            $table->bigInteger('ThirdCount')->nullable();
            $table->bigInteger('TotalXP')->nullable();
            $table->bigInteger('CurrentPosition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leader_boards');
    }
};
