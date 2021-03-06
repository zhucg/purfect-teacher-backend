<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConferencesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('conferences_users')) {
            Schema::create('conferences_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('conference_id')->comment('会议ID');
                $table->foreign('conference_id')->references('id')->on('conferences');
                $table->integer('user_id')->comment('参会人ID');
                $table->integer('school_id')->comment('学校ID');
                $table->integer('status')->default(0)->comment('状态 0:未签到 1:已签到 2:已签退');
                $table->date('date')->comment('会议当天时间')->nullable();
                $table->time('from')->comment('会议开始时间')->nullable();
                $table->time('to')->comment('会议结束时间')->nullable();
                $table->timestamp('begin')->comment('开始签到时间')->nullable();
                $table->timestamp('end')->comment('结束签到时间')->nullable();
                $table->timestamps();
            });
        }
        DB::statement(" ALTER TABLE conferences_users comment '会议参会人表' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conferences_users');
    }
}
