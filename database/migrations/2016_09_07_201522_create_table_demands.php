<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDemands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demands', function(Blueprint $table){
            $table->increments('id');
            $table->string('name', 50)->comment('name');
            $table->string('email', 100)->comment('email');
            $table->text('description')->nullable()->comment('Tell us shorty about your project');
            $table->string('ip', 15)->nullabe()->comment('IP');
            $table->string('browser_info', 256)->nullable()->comment('browser info');
            $table->string('page_url', 512)->nullable()->comment('Page URL with UTM-marks');
            $table->string('status', 32)->nullabe()->comment('status');
            $table->text('comment')->nullabe()->comment('comment');
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
        Schema::drop('demands');
    }
}
