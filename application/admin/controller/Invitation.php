<?php

namespace app\admin\controller;

use think\Validate;

class Invitation extends Common
{
    public function index($id)
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $invitation_data = \app\admin\model\InvitationModel::with('user')->where('community_id', $id)->select();
        $this->assign('community_id', $id);
        $this->assign('invitation_data', $invitation_data);
        return view();
    }

    public function create($community_id)
    {
        $community_id = input('community_id');
        $this->assign('community_id', $community_id);
        return view();
    }

    public function create_do()
    {
        $result = input('post.');
        $rule = [
            'invitation_title' => 'require',
            'invitation_content' => 'require',
         ];
        $msg = [
            'invitation_title.require' => '请输入标题',
            'invitation_content.require' => '请输入内容',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        if(empty(session('user'))){
            $cookie = cookie('user');
            $user_id = $cookie['id'];
        }else{
            $user_id = session('user.id');
        }
        $result['user_id'] = $user_id;
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $res = \app\admin\model\InvitationModel::create($result, true);
        if(!$res){
            $this->error('发帖失败');
        }else{
            $this->success('发帖成功','admin/community/index');
        }
    }

    public function update()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $data = \app\admin\model\InvitationModel::where('id', $invitation_id)->find();
        $this->assign('data', $data);
        return view();
    }

    public function update_do()
    {
        $result = input('post.');
        if(!$result) {
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $rule = [
            'invitation_title' => 'require',
            'invitation_content' => 'require',
        ];
        $msg = [
            'invitation_title.require' => '请输入标题',
            'invitation_content.require' => '请输入内容',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $res = \app\admin\model\InvitationModel::update($result, ['id' => $result['id']], true);
        if(!$res){
            $this->error("修改失败");
        }else{
            $this->success("修改成功",'admin/community/index');
        }
    }

    public function delete()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationModel::destroy($invitation_id);
        if(!$res){
            $this->error("删除失败");
        }else{
            $this->success("删除成功");
        }
    }

    public function info()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $data = \app\admin\model\InvitationModel::where('id', $invitation_id)->find();
        $this->assign('data', $data);
        return view();
    }

    public function ban()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationModel::where('id', $invitation_id)->update(['invitation_state' => 2]);
        if(!$res){
            $this->error('封禁失败');
        }else{
            $this->success('封禁成功');
        }
    }

    public function removeban()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\InvitationModel::where('id', $invitation_id)->update(['invitation_state' => 1]);
        if(!$res){
            $this->error('解除失败');
        }else{
            $this->success('解除成功');
        }
    }
}