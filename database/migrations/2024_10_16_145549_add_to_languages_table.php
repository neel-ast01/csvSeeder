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
        Schema::table('languages', function (Blueprint $table) {
            Schema::create('languages', function (Blueprint $table) {
                $table->bigIncrements('id'); // Ensure it is unsignedBigInteger
                $table->string('language_name');
                $table->timestamps();
            });
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            Schema::dropIfExists('languages');
        });
    }
};
