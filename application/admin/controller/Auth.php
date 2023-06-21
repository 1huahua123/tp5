<?php
namespace app\admin\controller;

class Auth extends Common
{
    /**
     * admin用户权限列表
     */

    public function index()
    {
        $auth = \app\admin\model\AuthModel::select();
        $auth = getTree($auth);
        $this->assign('auth',$auth);
        return view();
    }

    /**
     * 添加用户权限
     */
    public function create()
    {
        $top_nav = \app\admin\model\AuthModel::where(['pid' => 0])->select();
        $this->assign('top_nav',$top_nav);
        return view();
    }

    public function docreate()
    {
        $result = input('post.');
        $res = \app\admin\model\AuthModel::create($result,true);
        if($res){
            $this->success("添加成功",'auth/index');
        }else{
            echo '<script>alert("添加失败");window.history.back();</script>';
            exit();
        }
    }

    public function update()
    {
        $id = input('id');
        $top_nav =  \app\admin\model\AuthModel::where(['pid' => 0])->select();
        $res = \app\admin\model\AuthModel::where(['id' => $id])->find();
        $this->assign('res', $res);
        $this->assign('top_nav', $top_nav);
        return view();
    }

    public function doupdate()
    {
        $id = input('id');
        $result = input('post.');
        $updated_at = date('Y-m-d H:i:s',time());
        $result['updated_at'] = $updated_at;
        $res = \app\admin\model\AuthModel::where(['id'=>$id])->update($result,true);
        if($res){
            $this->success("修改成功",'auth/index');
        }else{
            echo '<script>alert("修改失败");window.history.back();</script>';
            exit();
        }

    }

    public function delete()
    {
        $id = input('id');
        $role_id = session('user.role_id');
        if(empty(session('user'))){
            $cookie = cookie('user');
            $role_id = $cookie['role_id'];
        }
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以删除!");window.history.back();</script>';
            exit();
        }else{
            \app\admin\model\AuthModel::destroy($id);
            echo '<script>alert("删除成功!");window.history.back();</script>';
            exit();
        }
    }

}