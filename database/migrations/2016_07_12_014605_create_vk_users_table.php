<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('vk_users', function(Blueprint $table){
            $table->increments('id');
            $table->integer('uid')->comment('uid');
            $table->string('first_name', 50)->comment('first name');
            $table->string('last_name', 50)->comment('last name');
            $table->string('deactivated', 8)->nullable()->comment('NULL | deleted | banned');
            $table->boolean('hidden')->nullable()->comment('profile hidden from outside of vk');
            $table->tinyInteger('sex')->nullable()->comment('1 - female | 2 - male | 0 - not specified');
            $table->string('bdate', 10)->nullable()->comment('ugly field, DD.MM.YYYY or DD.MM (if birth year is hidden)');
            $table->date('birthdate')->nullable()->comment('birthdate');
            $table->string('birth_month_day', 5)->nullable()->comment('MM.DD');
            $table->integer('city_id')->nullable()->comment('city id');
            $table->integer('country_id')->nullable()->comment('country id');
            $table->string('photo', 128)->nullable()->comment('photo url');
            $table->string('photo_medium', 128)->nullable()->comment('photo_medium url');
            $table->string('photo_big', 128)->nullable()->comment('photo_big url');
            $table->string('contacts', 100)->nullable()->comment('contacts');
            $table->timestamp('last_seen_time')->nullable()->comment('last seen online timestamp');
            $table->string('last_seen_platform', 5)->nullable()->comment('last seen platform');
            $table->integer('followers_count')->nullable()->comment('followers count');
            $table->string('domain', 32)->nullable()->comment('domain, e.g. pavel_durov | id12344321');
            $table->string('site', 255)->nullable()->comment('site');
            $table->string('creating_record_table', 24)->nullable()->comment('creating_record_table');
            $table->integer('creating_record_id')->nullable()->comment('creating_record_id');
            $table->string('updating_record_table', 24)->nullable()->comment('updating_record_table');
            $table->integer('updating_record_id')->nullable()->comment('updating_record_id');
            $table->timestamps();

            $table->unique(['uid'], 'uid');
            $table->index(['country_id', 'city_id', 'birthdate', 'sex'], 'country_city_birthdate_sex');
            $table->index(['country_id', 'city_id', 'sex'], 'country_city_sex');
            $table->index(['last_seen_time', 'sex'], 'last_seen_time_sex');
            $table->index(['birthdate', 'sex'], 'birthdate_sex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vk_users');
    }
}
