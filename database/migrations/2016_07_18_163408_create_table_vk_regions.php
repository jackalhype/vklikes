<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVkRegions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_regions', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->comment('country id');
            $table->integer('region_id')->comment('region id');
            $table->string('title', 100)->comment('region title');
            $table->string('title_en', 100)->comment('latin title');
            $table->timestamps();
            $table->unique(['country_id', 'region_id'], 'country_region');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vk_regions');
    }
}
