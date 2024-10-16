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
    Schema::create('categories', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('parent_category_id')->nullable()->index();
        $table->boolean('is_active')->nullable()->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->foreign('parent_category_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('no action');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            Schema::dropIfExists('categories');
        });
    }
};
