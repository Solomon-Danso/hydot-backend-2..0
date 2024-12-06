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
        Schema::table('admin_users', function (Blueprint $table) {
           $table->longText("Token")->nullable();
           $table->dateTime("TokenExpire")->nullable();
           $table->integer("LoginAttempt")->nullable();
           $table->boolean("IsBlocked")->default(false);
           $table->longText("ServerId")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            //
        });
    }
};
