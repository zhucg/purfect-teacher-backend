<?php

namespace App\Http\Controllers\Operator;

use App\Dao\Schools\DepartmentDao;
use App\Dao\Schools\InstituteDao;
use App\Http\Requests\School\DepartmentRequest;
use App\Http\Controllers\Controller;
use App\BusinessLogic\CampusListPage\Factory;
use App\Models\Schools\Department;
use App\Models\Schools\DepartmentAdviser;
use App\Utils\FlashMessageBuilder;
use App\Utils\JsonBuilder;

class DepartmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function majors(DepartmentRequest $request){
        $logic = Factory::GetLogic($request);
        // 查看系的列表

        $this->dataForView['parent'] = $logic->getParentModel();
        $this->dataForView['majors'] = $logic->getData();
        $this->dataForView['appendedParams'] = $logic->getAppendedParams();

        return view($logic->getViewPath(), $this->dataForView);
    }

    /**
     * 添加新的系的视图加载
     * @param DepartmentRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(DepartmentRequest $request){
        $dao = new InstituteDao($request->user());
        $this->dataForView['institute'] = $dao->getInstituteById($request->uuid());
        $this->dataForView['department'] = new Department();
        return view('school_manager.department.add', $this->dataForView);
    }

    /**
     * 系的编辑视图加载
     * @param DepartmentRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(DepartmentRequest $request){
        $departmentDao = new DepartmentDao($request->user());
        $department = $departmentDao->getDepartmentById($request->uuid());
        $this->dataForView['department'] = $department;
        $this->dataForView['institute'] = $department->institute;
        return view('school_manager.department.edit', $this->dataForView);
    }

    /**
     * 保存系的操作
     * @param DepartmentRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(DepartmentRequest $request){
        $majorData = $request->getFormData();
        $dao = new DepartmentDao($request->user());
        $uuid = $majorData['institute_id'];

        if(isset($majorData['id'])){
            // 更新学院的操作
            if($dao->updateDepartment($majorData)){
                FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, $majorData['name'].'系已经修改成功');
            }else{
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, $majorData['name'].'系修改失败, 请重新试一下');
            }
        }else{
            // 新增专业的操作
            if($dao->createDepartment($majorData)){
                FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, $majorData['name'].'系已经创建成功');
            }else{
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, $majorData['name'].'系创建失败, 请重新试一下');
            }
        }
        return redirect()->route('school_manager.institute.departments',['uuid'=>$uuid,'by'=>'institute']);
    }

    public function set_adviser(DepartmentRequest $request){
        if($request->isMethod('POST')){
            $adviserData = $request->getAdviserForm();
            $result = (new DepartmentDao())->setAdviser($adviserData);

            return $result->isSuccess() ? JsonBuilder::Success():JsonBuilder::Error($result->getMessage());
        }
        elseif ($request->isMethod('GET')){
            $this->dataForView['pageTitle'] = '设置系主任';
            $department = (new DepartmentDao())->getDepartmentById($request->get('department'));
            $this->dataForView['department'] = $department;

            if($department->adviser){
                $adviser = $department->adviser;
            }
            else{
                // 如果系主任记录还不存在, 那么构造一个新的
                $adviser = new DepartmentAdviser();
                $adviser->department_id = $department->id;
                $adviser->department_name = $department->name;
                $adviser->school_id = $request->session()->get('school.id');
            }
            $this->dataForView['adviser'] = $adviser;
            return view('school_manager.department.set_adviser',$this->dataForView);
        }
    }
}
