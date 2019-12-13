<?php


namespace App\Dao\Forum;


use App\Models\Forum\ForumComment;
use App\Models\Forum\ForumCommentReply;
use App\Models\Forum\ForumLike;
use App\Utils\JsonBuilder;
use App\Utils\Misc\ConfigurationTool;
use App\Utils\ReturnData\MessageBag;

class ForumCommentDao
{
    public function createComment($data) {
        $re =  ForumComment::create($data);
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'添加成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'添加失败');
        }
    }

    public function deleteComment($id, $userId) {
        $re =  ForumComment::where('id', $id)->where('user_id', $userId)->delete();
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'删除成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'删除失败');
        }
    }
    public function getComment($id) {
        return ForumComment::where('id', $id)->first();

    }
    public function createCommentReply($data) {
        $re =  ForumCommentReply::create($data);
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'添加成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'添加失败');
        }
    }

    public function deleteCommentReply($id, $userId) {
        $re =  ForumCommentReply::where('id', $id)->where('user_id', $userId)->delete();
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'删除成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'删除失败');
        }
    }

    public function addForumLike($forumId,$userId) {
        $re =  ForumLike::create([
            'forum_id' => $forumId,
            'user_id'  => $userId
        ]);
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'添加成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'添加失败');
        }
    }
    public function deleteForumLike($forumId,$userId) {
        $re =  ForumLike::where('forum_id', $forumId)->where('user_id', $userId)->delete();
        if($re){
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'删除成功');
        } else {
            return new MessageBag(JsonBuilder::CODE_ERROR,'删除失败');
        }
    }


    public function getCommentForForum($forumId, $pageSize=ConfigurationTool::DEFAULT_PAGE_SIZE)
    {
        return ForumComment::where('forum_id', $forumId)->orderBy('id','DESC')->simplePaginate($pageSize);
    }


}
