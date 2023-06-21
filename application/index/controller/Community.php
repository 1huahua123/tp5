<?php
namespace app\index\controller;

use think\Validate;

class Community extends Common
{
    public function index()
    {
        $community_data = \app\index\model\CommunityModel::with('user')->where('deleted_at', null)->paginate(5);
        $data = $community_data->render();
        $this->assign('data', $data);
        $this->assign('community_data', $community_data);
        return view();
    }

    /*
     * 关注社区
     *
     * */
    public function interest()
    {
        $community_id = input('community_id');
        if(!$community_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $result = array();
        $result['user_id'] = $user_id;
        $result['community_id'] = $community_id;
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $res = \app\index\model\CommunityUserModel::create($result, true);
        if(!$res){
            echo '<script>alert("关注失败");window.history.back();</script>';
            exit();
        }else{
            $community_data = \app\index\model\CommunityModel::where('id', $community_id)->find();
            $community_data['community_followers'] += 1;
            \app\index\model\CommunityModel::where('id', $community_id)->update(['community_followers' => $community_data['community_followers']]);
            echo '<script>window.history.back();</script>';
            exit();
        }
    }

    public function uninterest()
    {
        $community_id = input('community_id');
        if(!$community_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登陆','index/Login/login');
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $res = \app\index\model\CommunityUserModel::where('user_id', $user_id)->where('community_id', $community_id)->delete();
        if(!$res){
            echo '<script>alert("网络错误请稍后重试");window.history.back();</script>';
            exit();
        }else{
            $community_data = \app\index\model\CommunityModel::where('id', $community_id)->find();
            $community_data['community_followers'] -= 1;
            \app\index\model\CommunityModel::where('id', $community_id)->update(['community_followers' => $community_data['community_followers']]);
            echo '<script>window.history.back();</script>';
            exit();
        }
    }

    public function create_send_invitation()
    {
        $community_id = input('community_id');
        if(!$community_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $this->assign('community_id', $community_id);
        return view();
    }

    public function send_invitation()
    {
        $community_id = input('community_id');
        $result = input('post.');
        if(!$community_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $community_user_data = \app\index\model\CommunityUserModel::where('user_id', $user_id)->where('community_id', $community_id)->find();
        if(!$community_user_data){
            $this->error('请先关注社区');
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
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $result['user_id'] = $user_id;
        $result['community_id'] = $community_id;
        $res = \app\index\model\InvitationModel::create($result, true);
        if(!$res){
            echo '<script>alert("网络出错，发帖失败");window.history.back();</script>';
            exit();
        }else{
            $this->success('发帖成功','index/community/index');
        }
    }

    public function search()
    {
        $result = input('post.');
        $keyword = $result['keywords'];
        $community_data = \app\index\model\CommunityModel::with('user')
            ->where('community_name', 'like', '%'.$keyword.'%')
            ->where('deleted_at', null)
            ->paginate(5);
        $this->assign('community_data', $community_data);
        $data = $community_data->render();
        $this->assign('data', $data);
        return view();
    }
}