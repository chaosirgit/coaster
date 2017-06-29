<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationship', function (Blueprint $table) {
            $table->increments('id');
	    $table->integer('from_uid')->nullable();
	    $table->integer('uid')->nullable();
	    $table->integer('to_uid')->nullable();
	    $table->datetime('start_time')->nullable();
	    $table->integer('status');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relationship');
    }
}
