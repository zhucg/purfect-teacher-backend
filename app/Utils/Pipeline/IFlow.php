<?php
/**
 * 流程审批的抽象设计
 * User: justinwang
 * Date: 1/12/19
 * Time: 11:17 PM
 */

namespace App\Utils\Pipeline;

interface IFlow extends IPersistent
{
    // 以下随便定义了几个流程的分类
    const TYPE_OFFICE = 1;
    const TYPE_2 = 2;
    const TYPE_3 = 3;
    const TYPE_4 = 4;
    const TYPE_TEACHER_ONLY = 5;

    const TYPE_STUDENT_ONLY = 6;
    const TYPE_FINANCE      = 7;
    const TYPE_STUDENT_COMMON = 8;

    const TYPE_OFFICE_TXT = '行政管理';
    const TYPE_2_TXT = '流程应用';
    const TYPE_3_TXT = '内外勤管理';
    const TYPE_4_TXT = '公文流转';
    const TYPE_TEACHER_ONLY_TXT = '教师专用';

    // 这三个是学生版办事大厅的种类
    const TYPE_STUDENT_ONLY_TXT = '学生专用';
    const TYPE_FINANCE_TXT      = '资助中心';
    const TYPE_STUDENT_COMMON_TXT  = '日常申请';

    /**
     * 获取流程的第一步
     * @return INode|null
     */
    public function getHeadNode();

    /**
     * 获取流程的最后一步
     * @return INode|null
     */
    public function getTailNode();

    /**
     * 获取流程名称
     * @return string
     */
    public function getName();

    /**
     * 流程是否可以被指定的用户 user 启动
     * @param IUser $user
     * @return INode
     */
    public function canBeStartBy(IUser $user): INode;

    /**
     * 返回当前流程中等待处理的步骤
     * @param IUser $user
     * @return IAction
     */
    public function getCurrentPendingAction(IUser $user): IAction;

    /**
     * 设置传入的 node 为当前流程
     *
     * @param INode $node
     * @param IUser $user
     * @return boolean
     */
    public function setCurrentPendingNode(INode $node, IUser $user);
}