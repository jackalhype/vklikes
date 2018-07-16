<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVklikesRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vklikes_requests', function(Blueprint $table){
            $table->increments('id');
            $table->string('vk_url', 512)->comment('vk.vom Post URL.');
            $table->string('filter')->comment('likes / copies (reposts)');

            $table->integer('total_users')->nullable()->comment('Number of users in likes/reposts collection');
            $table->string('requests_count')->nullable()->comment('Fact cURL requests number.');
            $table->boolean('is_error')->default(false)->comment('error occured');
            $table->string('error_str', 512)->nullable()->comment('error string');

            $table->string('user_session_id', 32)->nullable()->comment('user session id');
            $table->string('user_http_user_agent')->nullable()->comment('browser info');
            $table->string('user_remote_addr')->nullable()->comment('user IP');
            $table->string('user_remote_port')->nullable()->comment('user port');

            $table->dateTime('jobs_started_at')->nullable()->comment('parsing started');
            $table->dateTime('jobs_finished_at')->nullable()->comment('parsing finished');

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
        Schema::drop('vklikes_requests');
    }
}
