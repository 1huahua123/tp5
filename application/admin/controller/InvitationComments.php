<?php

namespace app\admin\controller;

class InvitationComments extends Common
{
    public function index()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $invitation_comments = \app\admin\model\InvitationCommentsModel::with('user,invitation')->where('invitation_id', $invitation_id)->select();
        $this->assign('invitation_comments', $invitation_comments);
        $community_id = \app\admin\model\InvitationModel::where('id', $invitation_id)->field('community_id')->find();
        $this->assign('community_id', $community_id['community_id']);
        return view();
    }

    public function delete()
    {
        $invitation_comments_id = input('invitation_comments_id');
        if(!$invitation_comments_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationCommentsModel::destroy($invitation_comments_id);
        if(!$res){
            $this->error('删除失败');
        }else{
            $this->success('删除成功');
        }
    }

    /*
     * 封禁评论
     * */
    public function ban()
    {
        $invitation_comments_id = input('invitation_comments_id');
        if(!$invitation_comments_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationCommentsModel::update(['comment_state' => 2], ['id' => $invitation_comments_id]);
        if(!$res){
            $this->error('封禁失败');
        }else{
            $this->success('封禁成功');
        }
    }

    /*
     * 解除评论封禁
     * */
    public function removeBan()
    {
        $invitation_comments_id = input('invitation_comments_id');
        if(!$invitation_comments_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationCommentsModel::update(['comment_state' => 1], ['id' => $invitation_comments_id]);
        if(!$res){
            $this->error('解除失败');
        }else{
            $this->success('解除成功');
        }
    }

}