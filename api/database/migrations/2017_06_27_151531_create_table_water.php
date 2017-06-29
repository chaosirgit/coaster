<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWater extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('water', function (Blueprint $table) {
		$table->increments('id');
		$table->integer('uid');
		$table->timestamp('creat_at');
		$table->datetime('drink_date')->nullable();
		$table->integer('drink_water')->nullable();
		$table->datetime('suggest_date')->nullable();
		$table->integer('suggest_water')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('water');
    }
}
