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
        Schema::create('what_we_dos', function (Blueprint $table) {
            $table->id();
            $table->longText("Main_Title")->nullable();
            $table->longText("Secondary_Title")->nullable();

            $table->longText("Left_Main_Title")->nullable();
            $table->longText("Left_Secondary_Title")->nullable();
            $table->longText("Left_Text1")->nullable();
            $table->longText("Left_Text2")->nullable();
            $table->longText("Left_Text3")->nullable();

            $table->longText("Right_Main_Title")->nullable();
            $table->longText("Right_Secondary_Title")->nullable();
            $table->longText("Right_Text1")->nullable();
            $table->longText("Right_Text2")->nullable();
            $table->longText("Right_Text3")->nullable();

            $table->longText("Middle_Main_Title")->nullable();
            $table->longText("Middle_Secondary_Title")->nullable();
            $table->longText("Middle_Text1")->nullable();
            $table->longText("Middle_Text2")->nullable();
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('what_we_dos');
    }
};
