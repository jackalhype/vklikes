<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVkCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_cities', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->comment('country id');
            $table->integer('region_id')->comment('region id');
            $table->integer('cid')->comment('city id');
            $table->string('title', 100)->comment('city title');
            $table->string('title_en', 100)->comment('latin title');
            $table->string('area', 100)->nullable()->comment('area. Region can have few of them.');
            $table->string('region', 100)->nullable()->comment('region title');
            $table->timestamps();
            $table->unique(['country_id', 'region_id', 'cid'], 'country_region_city');
            $table->unique(['country_id', 'cid'], 'country_city');
            $table->unique(['cid'], 'cid');
            $table->index(['country_id', 'region_id', 'title'], 'country_region_title');
            $table->index(['country_id', 'title'], 'country_title');
            $table->index(['country_id', 'region_id', 'title_en'], 'country_region_title_en');
            $table->index(['country_id', 'title_en'], 'country_title_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vk_cities');
    }
}
