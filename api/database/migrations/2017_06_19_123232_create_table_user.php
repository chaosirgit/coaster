<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id');
	    $table->timestamps();	//该方法会自动在表里建立 created_at 与 updated_at;
	    $table->string('phone');
	    $table->string('password');
	    $table->boolean('gender');
	    $table->string('nickname')->nullable();
	    $table->date('birthday')->nullable();	//使用列修改器允许为null
	    $table->integer('height')->nullable();
	    $table->integer('weight')->nullable();
	    $table->string('email')->nullable();
	    $table->string('deviceid')->nullable();
	    $table->longText('image')->nullable();
        });
    	
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
