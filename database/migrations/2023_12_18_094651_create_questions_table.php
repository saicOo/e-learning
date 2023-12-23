<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('image')->nullable();
            $table->integer('grade');
            $table->json('options')->nullable(); // JSON array of answer options
            $table->integer('correct_option')->nullable(); // Index of the correct option in the 'options' array
            $table->tinyInteger('type')->comment('1=>TrueFalse, 2=>Choice,3 =>Article');
            $table->unsignedBigInteger('listen_id');
            $table->foreign('listen_id')->references('id')->on('listens')->onDelete('cascade');
            $table->unsignedBigInteger('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
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
        Schema::dropIfExists('questions');
    }
}
