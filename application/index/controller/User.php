<?php

namespace app\index\controller;

use think\Validate;

class User extends Common
{
    public function index()
    {
        $user_id = input('id');
//        我的信息
        $user_data = \app\index\model\UserModel::where('state', 1)
            ->where('id', $user_id)
            ->select();
        $this->assign('user_data', $user_data);
//        我发布的资讯
        $blog_user_data = \app\index\model\BlogModel::where('blog_state', 1)
            ->where('deleted_at', null)
            ->where('blog_author', $user_id)
            ->paginate(4);
        $blog_data = $blog_user_data->render();
        $this->assign('blog_data', $blog_data);
        $this->assign('blog_user_data', $blog_user_data);
//        我发布的帖子
        $invitation_user_data = \app\index\model\InvitationModel::where('invitation_state', 1)
            ->where('deleted_at', null)
            ->where('user_id', $user_id)
            ->limit(4)
            ->select();
        $this->assign('invitation_user_data', $invitation_user_data);
        return view();
    }

    public function setup()
    {
        $user_id = input('id');
        $u_id = session('userinfo.id');
        if (empty($u_id)) {
            $cookie = cookie('userinfo');
            $u_id = $cookie['id'];
        }
        if ($user_id !== $u_id) {
            $this->error('非法操作');
        }
        $user_data = \app\index\model\UserModel::where('state', 1)
            ->where('id', $user_id)
            ->field('id, username, avatar, thumb_avatar, user_label, birthday')
            ->find();
        $this->assign('user_data', $user_data);
        return view();
    }

    public function doSetup()
    {
        $result = input('post.');
        $cookie = cookie('userinfo');
        $u_id = empty(session('userinfo.id')) ? $cookie['id'] : session('userinfo.id');
        if ($u_id !== $result['user_id']) {
            $this->error('非法操作!');
        }
        $rule = [
            'avatar' => 'require',
            'thumb_avatar' => 'require',
            'username' => 'require',
            'user_label' => 'max:100',
        ];
        $msg = [
            'avatar.require' => '请上传头像',
            'thumb_avatar' => '请上传头像',
            'username' => '请输入名称',
            'user_label.max' => '标签太长了',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($result)) {
            $error = $validate->getError();
            $this->error($error);
        }
        unset($result['user_id']);
        $res = \app\index\model\UserModel::where('state', 1)
            ->where('id', $u_id)
            ->update($result, true);
        if ($res) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
}