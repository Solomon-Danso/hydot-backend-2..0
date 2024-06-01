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
        Schema::create('our_differences', function (Blueprint $table) {
            $table->id();
            $table->longText("Main_Title")->nullable();
            $table->longText("Secondary_Title")->nullable();

            $table->longText("Left_Main_Title")->nullable();
            $table->longText("Left_Description")->nullable();
          
            $table->longText("Right_Main_Title")->nullable();
            $table->longText("Right_Description")->nullable();
           
            $table->longText("Middle_Main_Title")->nullable();
            $table->longText("Middle_Description")->nullable();
                      $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_differences');
    }
};
