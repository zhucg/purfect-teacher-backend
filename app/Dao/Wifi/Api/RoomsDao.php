<?php
/**
 * Created by PhpStorm.
 * User: zhang.kui
 * Date: 18/10/19
 * Time: 11:47 AM
 */
namespace App\Dao\Wifi\Api;

use App\Models\Wifi\Api\Rooms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Schools\Building;

class RoomsDao extends CommonDao
{
   /**
    * Func:  添加或更新数据
    * return 主键ID|false
    */
   public static function addOrUpdateRoomsInfo( $data = [] , $contentid = null )
   {
      return Rooms::addOrUpdateRoomsInfo ( $data , $contentid );
   }

   /**
    * Func:  删除数据
    * return true|false
    */
   public static function delRoomsInfo( $condition = [] , $saveFieldData = [] , $isDelete = false )
   {
      return Rooms::delRoomsInfo ( $condition , $saveFieldData , $isDelete );
   }

   /**
    * Func 获取单条数据
    * return array
    */
   public static function getRoomsOneInfo ( $condition = [] , $orderArr = [] , $fieldsArr = [] , $joinArr = [] )
   {
      return Rooms::getRoomsOneInfo ( $condition , $orderArr , $fieldsArr , $joinArr );
   }

   /**
    * Func 获取多条数据
    * return array
    */
   public static function getRoomsListInfo ( $condition = [] , $orderArr = [] , $pageArr = [] , $fieldsArr = [] , $joinArr = [] )
   {
      return Rooms::getRoomsListInfo ( $condition , $orderArr , $pageArr , $fieldsArr , $joinArr );
   }

   /**
    * Func 统计数据
    * return Int
    */
   public static function getRoomsStatistics ( $condition = [] , $field = null )
   {
      return Rooms::getRoomsStatistics ( $condition , $field );
   }

   /**
    * Func:  获取宿舍楼地址列表
    * @Param $campus_id Int 校区
    * @Param $type Int 1:宿舍楼
    * return Array
    */
   /**
    * Func:  获取宿舍楼地址列表
    * @Param $campus_id Int 校区
    * @Param $type Int 1:宿舍楼
    * return Array
    */
   public static function getRoomsListMoreInfo( $campus_id = 0 )
   {
       if (!intval($campus_id)) return [];
       $data = Building::whereNull('deleted_at')
           ->where('campus_id', $campus_id)
           ->with('rooms')
           ->get();
       if (!empty($data)) {
           foreach ($data as $key => &$val) {
               $val->name = $val->typeArr[$val->type] . '(' . $val->name . ')';
           }
       }
       return $data;
   }
}
