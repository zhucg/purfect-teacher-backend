<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamsPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('exams_plans_rooms')) {
            Schema::create('exams_plans_rooms', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('plan_id')->comment('考试计划ID');
                $table->foreign('plan_id')->references('id')->on('exams_plans');
                $table->unsignedInteger('room_id')->comment('教室ID');
                $table->foreign('room_id')->references('id')->on('rooms');
                $table->timestamp('from')->comment('开始时间');
                $table->timestamp('to')->nullable()->comment('结束时间');
                $table->smallInteger('num')->comment('容纳人数');
                $table->integer('first_teacher_id')->comment('主监考id')->nullable();
                $table->string('first_invigilate',30)->comment('主监考')->nullable();
                $table->integer('second_teacher_id')->comment('副监考id')->nullable();
                $table->string('second_invigilate',30)->comment('副监考')->nullable();
                $table->integer('thirdly_teacher_ide')->comment('副监考id')->nullable();
                $table->string('thirdly_invigilate',30)->comment('副监考')->nullable();
                $table->timestamps();
            });
        }
        DB::statement(" ALTER TABLE exams_plans_rooms comment '考场表' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exams_plans_rooms');
    }
}
