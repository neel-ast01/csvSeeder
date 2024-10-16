<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('sys_url', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->enum('type', ['1', '2', '3']);
        $table->unsignedBigInteger('target_id');
        $table->string('target_type');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_url', function (Blueprint $table) {
            Schema::dropIfExists('sys_url');
        });
    }
};
