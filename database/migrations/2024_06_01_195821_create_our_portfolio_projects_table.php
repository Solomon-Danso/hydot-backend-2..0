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
        Schema::create('our_portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->longText("Picture")->nullable();
            $table->longText("Link")->nullable();
            $table->longText("ProjectName")->nullable();
            $table->longText("ProjectId")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_portfolio_projects');
    }
};
