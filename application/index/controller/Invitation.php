<?php

namespace app\index\controller;

use think\Validate;

class Invitation extends Common
{
    /*
     * 社区帖子主页
     * */
    public function index()
    {
        $community_id = input('community_id');
        if(!$community_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $community_data = \app\index\model\CommunityModel::where('id', $community_id)
            ->where('deleted_at', null)
            ->find();
        $this->assign('community_data', $community_data);
        $invitation_data = \app\index\model\InvitationModel::with('user')
            ->where('deleted_at', null)
            ->where('invitation_state', 1)
            ->where('community_id', $community_id)
            ->order('created_at DESC')
            ->paginate(10);
        $this->assign('invitation_data', $invitation_data);
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->assign('community_user_interest', 1);
        }else {
            $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
            $interst_date = \app\index\model\CommunityUserModel::where('community_id', $community_id)
                ->where('user_id', $user_id)
                ->find();
            if(!$interst_date){
                $this->assign('community_user_interest', 1);
            }else{
                $this->assign('community_user_interest', 2);
            }
        }
        $invitation_data_nums = count($invitation_data);
        $data = $invitation_data->render();
        $this->assign('data', $data);
        $this->assign('invitation_data_nums', $invitation_data_nums);
        return view();
    }

    /*
     * 帖子详情页面
     * */
    public function info()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $invitation_data = \app\index\model\InvitationModel::with('user')
            ->where('deleted_at', null)
            ->where('invitation_state',1)
            ->where('id', $invitation_id)
            ->find();
        $this->assign('invitation_data', $invitation_data);
        $community_data = \app\index\model\CommunityModel::where('id', $invitation_data['community_id'])
            ->where('deleted_at', null)
            ->find();
        $this->assign('community_name', $community_data['community_name']);
        $this->assign('community_id', $community_data['id']);
//        检测是否点赞
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->assign('invitation_user_likes', 1);
        }else{
            $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
            $likes_data = \app\index\model\InvitationLikesModel::where('user_id', $user_id)
                ->where('invitation_id', $invitation_id)
                ->find();
            if(!$likes_data){
                $this->assign('invitation_user_likes', 1);
            }else{
                $this->assign('invitation_user_likes', 2);
            }
        }
        $invitation_comments_data = \app\index\model\InvitationCommentsModel::with('user')
            ->where('comment_state', 1)
            ->where('invitation_id', $invitation_id)
            ->select();
        $this->assign('invitation_comments_data', $invitation_comments_data);
        $invitation_comments_reply_data = \app\index\model\InvitationCommentsReplyModel::with('user,replyUser')
            ->where('reply_state', 1)
            ->where('invitation_id', $invitation_id)
            ->select();
        $this->assign('invitation_comments_reply_data', $invitation_comments_reply_data);
        $cookie = cookie('userinfo');
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $this->assign('user_id', $user_id);
        $this->assign('reply_user_id', $user_id);
        return view();
    }

    /*
     * 删除自己的评论
     * */
    public function delete_comment()
    {
        $invitation_comments_id = input('invitation_comments_id');
        if(!$invitation_comments_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $comments_res = \app\index\model\InvitationCommentsModel::where('id', $invitation_comments_id)
            ->where('user_id', $user_id)
            ->find();
        $invitation_data = \app\index\model\InvitationModel::where('id', $comments_res['invitation_id'])
            ->where('invitation_state', 1)
            ->find();
        $invitation_comments = $invitation_data['invitation_comments'] - 1;
        \app\index\model\InvitationModel::where('id', $comments_res['invitation_id'])
            ->where('invitation_state', 1)
            ->update(['invitation_comments' => $invitation_comments]);
        $comments_data = \app\index\model\InvitationCommentsModel::where('id', $invitation_comments_id)
            ->where('user_id', $user_id)
            ->delete();
        if(!$comments_data){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }else{
            echo '<script>window.history.back();</script>';
            exit();
        }
    }

    /*
     * 删除自己的回复
     * */
    public function delete_reply()
    {
        $invitation_reply_id = input('invitation_reply_id');
        if(!$invitation_reply_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $reply_res = \app\index\model\InvitationCommentsReplyModel::where('id', $invitation_reply_id)
            ->where('user_id', $user_id)
            ->where('reply_state', 1)
            ->find();
        $invitation_data = \app\index\model\InvitationModel::where('id', $reply_res['invitation_id'])
            ->where('invitation_state', 1)
            ->find();
        $invitation_comments_nums = $invitation_data['invitation_comments'] - 1;
        \app\index\model\InvitationModel::where('id', $reply_res['invitation_id'])
            ->where('invitation_state', 1)
            ->update(['invitation_comments' => $invitation_comments_nums]);
        $res = \app\index\model\InvitationCommentsReplyModel::where('id', $invitation_reply_id)
            ->where('user_id', $user_id)
            ->where('reply_state', 1)
            ->delete();
        if(!$res){
            echo '<script>alert("非法请求)</script>';
            exit();
        }else{
            echo '<script>window.history.back();</script>';
            exit();
        }
    }

    /*
     * 帖子点赞
     * */
    public function likes()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
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
        $result['invitation_id'] = $invitation_id;
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $res = \app\index\model\InvitationLikesModel::create($result, true);
        if(!$res){
            $this->error('点赞失败');
        }else{
            $invitation_data = \app\index\model\InvitationModel::where('id', $invitation_id)->find();
            $invitation_data['invitation_likes'] += 1;
            \app\index\model\InvitationModel::where('id', $invitation_id)
                ->update(['invitation_likes' => $invitation_data['invitation_likes']]);
            echo '<script>window.history.back();</script>';
            exit();
        }


    }

    /*
     * 取消点赞
     * */
    public function unlikes()
    {
        $invitation_id = input('invitation_id');
        if(!$invitation_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $user_id = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $likes_data = \app\index\model\InvitationLikesModel::where('user_id', $user_id)
            ->where('invitation_id', $invitation_id)
            ->delete();
        if(!$likes_data){
            $this->error('取消失败');
        }else{
            $invitation_data = \app\index\model\InvitationModel::where('id', $invitation_id)->find();
            $invitation_data['invitation_likes'] -= 1;
            \app\index\model\InvitationModel::where('id', $invitation_id)
                ->update(['invitation_likes' => $invitation_data['invitation_likes']]);
            echo '<script>window.history.back();</script>';
        }
    }

    /*
     * 帖子评论
     * */
    public function invitation_comments()
    {
        $result = input('post.');
        if(!$result['invitation_id']){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $rule = [
            'comment_content' => 'require',
        ];
        $msg = [
            'comment_content.require' => '请输入评论内容',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $result['user_id'] = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $result['user_ip'] = get_ip();
        $result['comment_level'] = 0;
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $res = \app\index\model\InvitationCommentsModel::create($result, true);
        if(!$res){
            $this->error('评论失败');
        }else{
            $invitation_data = \app\index\model\InvitationModel::where('id', $result['invitation_id'])->find();
            $invitation_comments = $invitation_data['invitation_comments'] + 1;
            \app\index\model\InvitationModel::where('id', $result['invitation_id'])
                ->update(['invitation_comments' => $invitation_comments]);
            echo '<script>window.history.back();</script>';
            exit();
        }
    }

    /*
     * 帖子评论回复
     * */
    public function invitation_comments_reply()
    {
        $result = input('post.');
        var_dump($result);
        exit();
        $reply_id = input('invitation_comments_id');
//        顶级评论用户ID
        $comment_userid = input('comment_userid');
        if(!$reply_id){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $rule = [
            'comment_content' => 'require',
        ];
        $msg = [
            'comment_content.require' => '请输入回复内容',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $result['user_id'] = empty($cookie) ? session('userinfo.id') : $cookie['id'];
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $invitation_comments_data = \app\index\model\InvitationCommentsModel::where('id', $reply_id)->find();
        $result['invitation_id'] = $invitation_comments_data['invitation_id'];
        $res = \app\index\model\InvitationCommentsReplyModel::create($result, true);
        if(!$res){
            $this->error('评论失败');
        }else{
            $invitation_id = $invitation_comments_data['invitation_id'];
            $invitation_data = \app\index\model\InvitationModel::where('id', $invitation_id)->find();
            $invitation_comments = $invitation_data['invitation_comments'] + 1;
            \app\index\model\InvitationModel::where('id', $invitation_id)
                ->update(['invitation_comments' => $invitation_comments]);
            $this->success('评论成功');
        }
    }

    /*
     * 吧内搜索帖子
     * */
    public function search()
    {
        $result = input('post.');
        if(!$result){
            echo '<script>alert("非法请求");window.history.back();</script>';
            exit();
        }
        $keyword = $result['keywords'];
        $invitation_data = \app\index\model\InvitationModel::with('user')
            ->where('invitation_title', 'like', '%'.$keyword.'%')
            ->where('invitation_state', 1)
            ->where('deleted_at', null)
            ->paginate(10);
        $this->assign('invitation_data', $invitation_data);
        $data = $invitation_data->render();
        $this->assign('data', $data);
        $community_data = \app\index\model\CommunityModel::with('user')
            ->where('id', $result['community_id'])
            ->find();
        $this->assign('community_data', $community_data);
        return view();
    }

}