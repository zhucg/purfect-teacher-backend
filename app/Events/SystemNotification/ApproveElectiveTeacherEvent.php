<?php

namespace App\Events\SystemNotification;

use App\Events\CanSendSystemNotification;
use App\Models\Course;
use App\Models\ElectiveCourses\TeacherApplyElectiveCourse;
use App\Models\Misc\SystemNotification;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ApproveElectiveTeacherEvent implements CanSendSystemNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private  $user;
    private  $apply;
    private  $result;

    /**
     * ApproveElectiveCourseEvent constructor.
     * @param User $user
     * @param $courseId
     */
    public function __construct(User $user, TeacherApplyElectiveCourse $apply, $result)
    {
        $this->user = $user;
        $this->apply = $apply;
        $this->result = $result;
    }

    /**
     * 必须可以拿到接收学校
     * @return int
     */
    public function getSchoolId(): int
    {
        return $this->user->getSchoolId();
    }

    /**
     * 可以拿到接收者
     * @return int
     */
    public function getTo(): int
    {
        return $this->user->id;
    }

    /**
     * 必须可以拿到发送者
     * @return int
     */
    public function getSender(): int
    {
        return SystemNotification::FROM_SYSTEM;//系统
    }

    /**
     * 必须可以拿到消息类别
     * @return int
     */
    public function getType(): int
    {
        return SystemNotification::TYPE_NONE;
    }

    /**
     * 必须可以拿到消息级别
     * @return int
     */
    public function getPriority(): int
    {
        return SystemNotification::PRIORITY_LOW;
    }
    /**
     * 必须可以拿到发送标题
     * @return string
     */
    public function getTitle(): string
    {
        return $this->result ? '你有一个新的课程安排！' : '你申请的选课未开课！';
    }

    /**
     * 必须可以拿到发送内容
     * @return string
     */
    public function getContent(): string
    {
        return '课程名称：' . $this->apply->name;
    }

    /**
     * 必须可以拿到消息分类
     * @return int
     */
    public function getCategory(): int
    {
        return SystemNotification::TEACHER_CATEGORY_COURSE;
    }

    /**
     * 必须可以拿到前端所需key
     * @return string
     */
    public function getAppExtra(): string
    {
        $extra = [
            'type' => $this->result ? 'oa-elective-info' : 'oa-elective-apply',
            'param1' => $this->apply->id,
            'param2' => ''
        ];
        return json_encode($extra);
    }

    /**
     * 必须可以拿到下一步
     * @return string
     */
    public function getNextMove(): string
    {
        return '';
    }

    /**
     * 必须可以拿到组织id
     * @return array
     */
    public function getOrganizationIdArray(): array
    {
        return [];//单人消息 无组织可见范围
    }

}
