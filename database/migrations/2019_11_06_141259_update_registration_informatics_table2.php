<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRegistrationInformaticsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('registration_informatics', function (Blueprint $table) {
            $table->unsignedBigInteger('last_updated_by')
                ->default(0)->comment('最后更新该数据的用户');
            $table->timestamp('approved_at')->nullable()->comment('录取时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_informatics', function (Blueprint $table) {
            // 报名信息
            $table->dropColumn('last_updated_by');
            $table->dropColumn('approved_at');
        });
    }
}
