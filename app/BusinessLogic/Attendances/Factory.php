<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/3/31
 * Time: 上午10:33
 */

namespace App\BusinessLogic\Attendances;



use App\BusinessLogic\Attendances\Impl\Leave;
use App\BusinessLogic\Attendances\Impl\SignIn;
use App\BusinessLogic\Attendances\Impl\Truant;

class Factory
{

    public static function GetStepLogic($leave, $currentUser, $userId) {
        // 判断当前状态是否为请假
        if(empty($leave)) {
            if(!is_null($currentUser) && $currentUser->id == $userId) {
                // 签到
                $logic = new SignIn();
            } else {
                return null ;
            }

        } else {
            // 请假
            $logic = new Leave();

        }

        return $logic;


    }

}