<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('author_id')->nullable();
            $table->string('title')->nullable();
            $table->dateTime('date')->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->enum('type', ['report_published'])->nullable(false);
            $table->string('target_id')->nullable();
            $table->json('data')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feeds');
    }
}
