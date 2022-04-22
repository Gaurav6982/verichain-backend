<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('fathers_name')->nullable();
            $table->string('mothers_name')->nullable();
            $table->enum('gender',['m','f'])->default('m');
            $table->string('mobile')->nullable();
            $table->integer('admission_session')->nullable();
            $table->string('institute_code')->nullable();
            $table->string('institute_name')->nullable();
            $table->string('course_name')->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('semester')->nullable();
            $table->string('branch')->nullable();
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
        Schema::dropIfExists('user_data');
    }
}
