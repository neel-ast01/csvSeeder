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
    Schema::create('category_translations', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('category_id')->index();
        $table->unsignedBigInteger('language_id')->index();  // Ensure this is unsignedBigInteger
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('meta_title')->nullable();
        $table->string('meta_keyword')->nullable();
        $table->text('meta_description')->nullable();
        $table->timestamps();
        $table->softDeletes();
    
        // Ensure the foreign key matches the data type of the referenced column
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('no action');
        $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade')->onUpdate('no action');
    });
}





    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_translations', function (Blueprint $table) {
            Schema::dropIfExists('category_translations');
        });
    }
};
