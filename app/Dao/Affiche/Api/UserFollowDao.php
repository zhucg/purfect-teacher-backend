<?php
/**
 * Created by PhpStorm.
 * User: zhang.kui
 * Date: 20/01/11
 * Time: 11:33 AM
 */
namespace App\Dao\Affiche\Api;

use App\Dao\Users\UserDao;
use App\Utils\JsonBuilder;

use App\Models\Affiche\UserFollow;
use App\Models\Affiche\AffichePraise;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Builder\UuidBuilderInterface;

class UserFollowDao extends \App\Dao\Affiche\CommonDao
{
    public function __construct()
    {
    }

    /**
     * Func 获取用户是否关注
     *
     * @param['user_id']  是   用户id
     * @param['touser_id'] 是  关注用户uid
     *
     * @return bool
     */
    public function getUserFollowCount($user_id = 0 , $touser_id = 0)
    {
        if( !intval($user_id) || !intval($touser_id))
        {
            return 0;
        }
        // 检索条件
        $condition[] = ['user_id','=',$user_id];
        $condition[] = ['touser_id','=',$touser_id];
        $count = UserFollow::where($condition)->count();
        if($count > 0){
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Func 添加和取消关注
     *
     * @param['user_id']  是  用户id
     * @param['touser_id'] 是 to 用户id
     *
     * @return bool
     */
    public function addOrUpdateUserFollowInfo($user_id = 0 , $touser_id = 0)
    {
        if ( !intval( $user_id ) || !intval( $touser_id ) )
        {
            return [ 'result' => 0 ];
        }

        // 检索条件
        $condition[] = ['user_id','=',$user_id];
        $condition[] = ['touser_id','=',$touser_id];
        $count = UserFollow::where($condition)->count();
        // 取消关注
        if($count > 0)
        {
            // 取消赞
            if(UserFollow::where($condition)->delete())
            {
                return [ 'result' => 0 ];
            } else {
                return [ 'result' => 1 ];
            }
        } else{
            // 添加关注
            $addData['user_id'] = $user_id;
            $addData['touser_id'] = $touser_id;
            $addData['created_at'] = date('Y-m-d H:i:s');
            if(UserFollow::create($addData))
            {
                return [ 'result' => 1 ];
            } else {
                return [ 'result' => 0 ];
            }
        }
        return [ 'result' => 0 ];
    }

}