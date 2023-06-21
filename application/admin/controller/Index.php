<?php
namespace app\admin\Controller;

use app\admin\controller\Common;
use think\Controller;


class Index extends Common
{
    public function index()
    {
        $user_nums = \app\admin\model\UserModel::where('deleted_at', null)
            ->count();
        $this->assign('user_nums', $user_nums);
        $admin_user_nums = \app\admin\model\UserModel::where('deleted_at', null)
            ->where('role_id', '<>', 5)
            ->count();
        $this->assign('admin_user_nums', $admin_user_nums);
        $blog_nums = \app\admin\model\BlogModel::where('deleted_at', null)
            ->count();
        $this->assign('blog_nums', $blog_nums);
        $invitation_nums = \app\admin\model\InvitationModel::where('deleted_at', null)
            ->count();
        $this->assign('invitation_nums', $invitation_nums);
        $blog_user_data = \app\admin\model\BlogModel::with('user,plate')
            ->where('deleted_at', null)
            ->order('created_at desc')
            ->limit(10)
            ->select();
        $this->assign('blog_user_data', $blog_user_data);
        return view();
    }
}