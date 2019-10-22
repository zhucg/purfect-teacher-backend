<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 21/10/19
 * Time: 8:37 AM
 */

namespace App\Dao\Schools;
use App\User;
use App\Models\Schools\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentDao
{
    private $currentUser;
    public function __construct(User $user)
    {
        $this->currentUser = $user;
    }

    /**
     * 根据给定的 campus 或 id 获取包含的学院
     * @param $institute
     * @return Collection
     */
    public function getByInstitute($institute){
        if(is_object($institute)){
            $institute = $institute->id;
        }
        return Department::where('institute_id',$institute)->get();
    }

    /**
     * @param $id
     * @return Department|null
     */
    public function getDepartmentById($id){
        return Department::find($id);
    }

    /**
     * 创建
     * @param $data
     * @return Department
     */
    public function createDepartment($data){
        $data['last_updated_by'] = $this->currentUser->id;
        return Department::create($data);
    }

    /**
     * @param $data
     * @param null $where
     * @param null $whereValue
     * @return mixed
     */
    public function updateDepartment($data, $where=null, $whereValue=null){
        $data['last_updated_by'] = $this->currentUser->id;
        $id = $data['id'];
        unset($data['id']);
        if($where && $whereValue){
            return Department::where($where,$whereValue)->update($data);
        }
        return Department::where('id',$id)->update($data);
    }
}