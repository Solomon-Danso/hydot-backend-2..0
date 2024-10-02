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
        Schema::create('schedule_emails', function (Blueprint $table) {
            $table->id();
            $table->longText("MessageId")->nullable();
            $table->longText("Target")->nullable();
            $table->longText("Email")->nullable();
            $table->longText("Message")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_emails');
    }
};
