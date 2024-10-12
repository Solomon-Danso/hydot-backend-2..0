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
        Schema::create('pre_paid_meters', function (Blueprint $table) {
            $table->id();
            $table->longText('Token')->nullable();
            $table->longText('ProductId')->nullable();
            $table->longText('ProductName')->nullable();
            $table->longText('PackageType')->nullable();
            $table->decimal('Amount')->nullable();
            $table->longText('apiHost')->nullable();
            $table->longText('apiKey')->nullable();
            $table->longText('softwareID')->nullable();
            $table->longText('companyId')->nullable();
            $table->longText('email')->nullable();
            $table->Date('ExpireDate')->nullable();



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_paid_meters');
    }
};
