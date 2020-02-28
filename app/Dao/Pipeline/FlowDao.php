<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 3/12/19
 * Time: 11:04 AM
 */

namespace App\Dao\Pipeline;

use App\Dao\Users\UserOrganizationDao;
use App\Models\Pipeline\Flow\Flow;
use App\Models\Pipeline\Flow\Node;
use App\Models\Pipeline\Flow\UserFlow;
use App\Models\Teachers\Teacher;
use App\Models\Users\UserOrganization;
use App\User;
use App\Utils\JsonBuilder;
use App\Utils\Misc\Contracts\Title;
use App\Utils\Pipeline\IAction;
use App\Utils\ReturnData\IMessageBag;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Support\Facades\DB;

class FlowDao
{
    /**
     * 获取分类的流程集合
     * @param $schoolId
     * @param array $types
     * @param boolean $forApp
     * @return array
     */
    public function getGroupedFlows($schoolId, $types = [], $forApp = false){
        $data = [];
        if(empty($types)){
            $types = array_keys(Flow::Types());
        }

        $flows = Flow::select(['id','name','icon','type'])
            ->where('school_id',$schoolId)
            ->whereIn('type',$types)
            ->orderBy('type','asc')->get();

        foreach ($types as $key){
            $data[$key] = [];
        }

        foreach ($flows as $flow) {
            if(in_array($flow->type, $types)){
                if($forApp){
                    $flow->icon = str_replace('.png','@2x.png',$flow->icon);
                }
                $data[$flow->type][] = $flow;
            }
        }

        $groups = [];

        foreach ($data as $key=>$items) {
            $groups[] = [
                'name'=>Flow::Types()[$key],
                'key'=>$key,
                'flows'=>$items
            ];
        }

        return $groups;
    }

    public function checkPermissionByuser(Flow $flow, User $user, $nodeId = 0) {
        $schoolId = $user->getSchoolId();
        if (empty($nodeId)) {
            $node = $flow->getHeadNode();
        }else {
            $node = Node::where('id',$nodeId)
                        ->with('handler')
                        ->with('attachments')
                        ->with('options')
                        ->first();
        }
        //验证目标群体
        $nodeSlugs = explode(';', trim($node->handler->role_slugs, ';'));
        if ($user->isStudent()) {
            $userSlug = '学生';
        }elseif ($user->isEmployee()) {
            $userSlug = '职工';
        }elseif ($user->isTeacher()) {
            $userSlug = '教师';
        }else {
            $userSlug = '';
        }
        if (!in_array($userSlug, $nodeSlugs)) {
            return false;
        }

        //验证组织
        if ($node->handler->organizations) {
            $nodeOrganizationArr = explode(';', trim($node->handler->organizations, ';'));

            $nodeTitlesArr = explode(';', trim($node->handler->titles, ';'));
            $whereIn = [];
            foreach ($nodeTitlesArr as $title) {
                if ($title == Title::ALL_TXT) {
                    $whereIn = [];
                    break;
                }
                if ($title == Title::ORGANIZATION_EMPLOYEE) {
                    $whereIn[] = Title::ORGANIZATION_EMPLOYEE_ID;
                }
                if ($title == Title::ORGANIZATION_DEPUTY) {
                    $whereIn[] = Title::ORGANIZATION_DEPUTY_ID;
                }
                if ($title == Title::ORGANIZATION_LEADER) {
                    $whereIn[] = Title::ORGANIZATION_LEADER_ID;
                }
            }

            $nodeOrganizations = UserOrganization::with(['organization' => function ($query) use ($nodeOrganizationArr, $schoolId){
                $query->where('school_id', $schoolId)->whereIn('name', $nodeOrganizationArr);
            }])->where('user_id', $user->id);
            if ($whereIn) {
                $nodeOrganizations->whereIn('title_id', $whereIn);
            }

            if ($nodeOrganizations->count() < 1) {
                return false;
            }
        }else {
            //职务
            $nodeTitlesArr = explode(';', trim($node->handler->titles, ';'));
            $check = false;//满足其中之一即可
            foreach ($nodeTitlesArr as $title) {
                //全体
                if ($title == Title::ALL_TXT) {
                    $check = true;
                    break;
                }
                //班主任
                if ($title == Title::CLASS_ADVISER) {
                    if (Teacher::myGradeManger($user->id)) {
                        $check = true;
                        break;
                    }
                }
                //年级组长
                if ($title == Title::GRADE_ADVISER) {
                    if (Teacher::myYearManger($user->id)) {
                        $check = true;
                        break;
                    }
                }
                //系主任
                if ($title == Title::DEPARTMENT_LEADER) {
                    if (Teacher::myDepartmentManger($user->id)) {
                        $check = true;
                        break;
                    }
                }
                //副校长
                if ($title == Title::SCHOOL_DEPUTY) {
                    $userOrganizationDao = new UserOrganizationDao();
                    $deputy = $userOrganizationDao->getDeputyPrinciples($schoolId);
                    if ($deputy) {
                        foreach ($deputy as $dep) {
                            if ($dep->user_id == $user->id) {
                                $check = true;
                                break 2;
                            }
                        }
                    }
                }
                //校长
                if ($title == Title::SCHOOL_PRINCIPAL) {
                    $userOrganizationDao = new UserOrganizationDao();
                    $principle = $userOrganizationDao->getPrinciple($schoolId);
                    if ($principle && $principle->user_id == $user->id) {
                        $check = true;
                        break;
                    }
                }
                //书记
                if ($title == Title::SCHOOL_COORDINATOR) {
                    $userOrganizationDao = new UserOrganizationDao();
                    $coordinator = $userOrganizationDao->getCoordinator($schoolId);
                    if ($coordinator && $coordinator->user_id == $user->id) {
                        $check = true;
                        break;
                    }
                }
            }
            if (!$check) {
                return false;
            }
        }
        return true;
    }

    /**
     * 开始一个流程
     * @param Flow|int $flow
     * @param User|int $user
     * @return IMessageBag
     */
    public function start($flow, $user){
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        $flowId = $flow;
        if(is_object($flow)){
            $flowId = $flow->id;
        }
        // 获取第一个 node
        $nodeDao = new NodeDao();
        $headNode = $nodeDao->getHeadNodeByFlow($flow);
        if($headNode){
            $actionData = [
                'flow_id'=>$flowId,
                'user_id'=>$user->id??$user,
                'node_id'=>$headNode->id,
                'result'=>IAction::RESULT_PENDING,
            ];

            DB::beginTransaction();

            try{
                $userFlow = UserFlow::create(
                    ['flow_id' => $flowId, 'user_id' => $user->id??$user]
                );
                $actionDao = new ActionDao();
                $action = $actionDao->create($actionData, $userFlow);
                $bag->setData($action);
                $bag->setCode(JsonBuilder::CODE_SUCCESS);
                DB::commit();
            }
            catch (\Exception $exception){
                DB::rollBack();
                $bag->setMessage($exception->getMessage());
            }
            return $bag;
        }

        $bag->setMessage('找不到指定的流程的第一步');
        return $bag;
    }

    /**
     * @param $id
     * @return Flow
     */
    public function getById($id){
        return Flow::find($id);
    }

    /**
     * 创建流程, 那么应该同时创建第一步, "发起" node. 也就是表示, 任意流程, 创建时会默认创建头部
     * @param $data
     * @param string $headNodeDescription
     * @param array $nodeAndHandlersDescriptor: 该流程可以由谁来发起, 如果为空数组, 表示可以由任何人发起.
     * @return IMessageBag
     */
    public function create($data, $headNodeDescription = '', $nodeAndHandlersDescriptor = []){
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);

        DB::beginTransaction();

        try{
            $flow = Flow::create($data);
            $nodeDao = new NodeDao();
            // 创建流程后, 默认必须创建一个"发起"的步骤作为第一步
            $headNode = $nodeDao->insert([
                'name'=>'发起'.$flow->name.'流程',
                'description'=>$headNodeDescription,
                'attachments'=> $nodeAndHandlersDescriptor['attachments'] ?? ''
            ], $flow);

            // 创建头部流程的 handlers
            $handlerDao = new HandlerDao();
            $handlerDao->create($headNode, $nodeAndHandlersDescriptor);

            DB::commit();
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
            $bag->setData($flow);
            return $bag;
        }
        catch (\Exception $exception){
            DB::rollBack();
            $bag->setMessage($exception->getMessage());
            return $bag;
        }
    }

    /**
     * @param $flowId
     * @return mixed
     */
    public function delete($flowId){
        return Flow::where('id',$flowId)->delete();
    }
}
