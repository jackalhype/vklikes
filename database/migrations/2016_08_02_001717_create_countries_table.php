<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_countries', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('country id');
            $table->string('title', 100)->comment('russian country title');
            $table->string('title_en', 100)->comment('english country title');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at');
            $table->unique(['cid'], 'cid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vk_countries');
    }
}
