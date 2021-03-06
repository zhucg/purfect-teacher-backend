<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Dao\Timetable\TimeSlotDao;
use App\Models\Timetable\TimeSlot;

class CreateTimeSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedTinyInteger('type'); // 上课, 课件休息, 自习, 午休, 课间操 等时间段的类型
            $table->time('from');
            $table->time('to')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

//        $dao = new TimeSlotDao();
//
//        $frames = $dao->getDefaultTimeFrame(TimeSlot::SEASONS_WINTER_AND_SPRINT)['frames'];
//
//        foreach ($frames as $frame) {
//            $frame['school_id'] = 1;
//            $frame['season'] = TimeSlot::SEASONS_WINTER_AND_SPRINT;
//            $dao->createTimeSlot($frame);
//        }
//
//        $frames = $dao->getDefaultTimeFrame(TimeSlot::SEASONS_SUMMER_AND_AUTUMN)['frames'];
//
//        foreach ($frames as $frame) {
//            $frame['school_id'] = 1;
//            $frame['season'] = TimeSlot::SEASONS_SUMMER_AND_AUTUMN;
//            $dao->createTimeSlot($frame);
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_slots');
    }
}
