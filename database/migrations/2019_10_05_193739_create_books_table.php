<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index();
            $table->year('year')->index();
            $table->unsignedSmallInteger('pages')->nullable();
            $table->char('isbn10', 10)->nullable();
            $table->char('isbn13', 13)->nullable();
            $table->char('lang', 3)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('books', static function (Blueprint $table) {
            $table->bigInteger('author_id')->unsigned()->index()->nullable();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
