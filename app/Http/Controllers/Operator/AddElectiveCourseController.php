<?php

namespace App\Http\Controllers\Operator;

use App\Dao\ElectiveCourses\TeacherApplyElectiveCourseDao;
use App\Http\Requests\School\TeacherApplyElectiveCourseRequest;
use App\Models\ElectiveCourses\ApplyCourseArrangement;
use App\Models\ElectiveCourses\TeacherApplyElectiveCourse;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddElectiveCourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 教师创建申请页面
     * @param TeacherApplyElectiveCourseRequest $request
     * @return string
     */
    public function create(TeacherApplyElectiveCourseRequest $request)
    {

        $validated = $request->validated();
        //获取当前学校的教师数据，实际给定的是user对象
        $user = $request->user();
        $schoolId = $user->getSchoolId()??$request->session()->get('school.id');
        if (empty($schoolId))
        {
            return  JsonBuilder::Error('没有获取到学校数据，请重试');
        }

        $applyData = $validated;
        $dao = new TeacherApplyElectiveCourseDao();
        $applyData['course']['school_id'] = $schoolId;
        $applyData['course']['max_num'] = $applyData['course']['max_number'];
        $applyData['course']['start_year'] = $applyData['course']['start_year']??date("Y");
        // 创建新选修课程申请
        $applyData['course']['status'] = $applyData['course']['status'] ?? $dao->getDefaultStatusByRole($user);
        $result = $dao->createTeacherApplyElectiveCourse($applyData);
        $apply = $result->getData();
        //如果是学校管理员添加的申请那么直接发布
        if ($result->isSuccess() && $dao->getDefaultStatusByRole($user) == TeacherApplyElectiveCourse::STATUS_VERIFIED)
        {
            $result  = $dao->publishToCourse($apply->id);
        }
        return $result->isSuccess() ?
            JsonBuilder::Success(['id' => $apply->id])
            : JsonBuilder::Error($result->getMessage());
    }

    /**
     * 将通过申请的选修课发布到课程course中
     * http://teacher.backend.com/api/elective-course/publish/22
     * @param Request $request
     * @return string
     */
    public function approve(Request $request)
    {
        $validatedData = $request->validate([
            'reply_content' => 'nullable|max:255',
        ]);
        $id = $request->get('course_id');

        $schoolId = $request->session()->get('school.id');

        $applyDao = new TeacherApplyElectiveCourseDao();
        $apply = $applyDao->getApplyById($id);
        if ($apply->school_id !== $schoolId) {
            return JsonBuilder::Error('您没有权限修改这个申请');
        }
        $content = $validatedData['reply_content']??'同意';

        $schedule = $request->get('schedule');

        //选修课表要求必须有building_id和classroom_id
        if (empty($schedule)) {
            return JsonBuilder::Error('请完善教室信息！');
        }
        foreach ($schedule as $item) {
            if (empty($item['building_id']) || empty($item['classroom_id'])) {
                return JsonBuilder::Error('请完善教室信息！');
            }
        }

        if ($applyDao->checkTimeConflictByTeacherId($schedule, $apply->start_year, $apply->term, $apply->teacher_id)){
            return JsonBuilder::Error('授课时间冲突');
        }

        $applyDao->approvedApply($id, $content, $schedule);
        $result  = $applyDao->publishToCourse($id);
        if($request->ajax()){
            return $result->isSuccess() ?
                JsonBuilder::Success(['id'=>$id])
                : JsonBuilder::Error($result->getMessage());
        }
        else{
            return redirect()->route('school_manager.elective-course.manager',['uuid'=>session('school.uuid')]);
        }
    }

    /**
     * 拒绝某个选修课的申请
     * @param Request $request
     * @return string
     */
    public function refuse(Request $request){
        $validatedData = $request->validate([
            'reply_content' => 'nullable|max:255',
        ]);
        $schoolId = $request->session()->get('school.id');
        $applyDao = new TeacherApplyElectiveCourseDao();
        $apply = $applyDao->getApplyById($request->get('course_id'));
        if ($apply->school_id !== $schoolId) {
            return JsonBuilder::Error('您没有权限修改这个申请');
        }
        $rejected = $applyDao->rejectedApply($request->get('course_id'), $validatedData['reply_content']??'拒绝');
        if($request->ajax()){
            return $rejected->isSuccess() ?
                JsonBuilder::Success()
                : JsonBuilder::Error($rejected->getMessage());
        }
        else{
            return redirect()->route('school_manager.elective-course.manager',['uuid'=>session('school.uuid')]);
        }
    }

    public function dissolved(Request $request){
        $schoolId = $request->session()->get('school.id');
        $applyDao = new TeacherApplyElectiveCourseDao();
        $apply = $applyDao->getApplyById($request->get('course_id'));
        if ($apply->school_id !== $schoolId) {
            return JsonBuilder::Error('您没有权限修改这个申请');
        }
        $dissolved = $applyDao->discolved($apply->course_id);
        if($request->ajax()){
            return $dissolved->isSuccess() ?
                JsonBuilder::Success()
                : JsonBuilder::Error($dissolved->getMessage());
        }
        else{
            return redirect()->route('school_manager.elective-course.manager',['uuid'=>session('school.uuid')]);
        }
    }
}
