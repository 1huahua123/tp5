<?php
namespace app\admin\Controller;

use app\admin\controller\Common;

class Role extends Common
{
    public function index()
    {
        $result = \app\admin\model\RoleModel::select();
        $this->assign('data', $result);
        return view();
    }

    public function create()
    {
        return view();
    }

    public function docreate()
    {
        $result = input('post.');
        $res = \app\admin\model\RoleModel::create(['role_name'=>$result['role_name']]);
        if($res){
            $this->success("添加成功",'role/index');
        }else{
            echo '<script>alert("添加失败");window.history.back();</script>';
            exit();
        }
    }

    public function delete()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty($cookie) ? session('user.role_id') : $cookie['role_id'];
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以删除!");window.history.back();</script>';
            exit();
        }else{
            \app\admin\model\RoleModel::destroy($id);
            echo '<script>alert("删除成功!");window.history.back();</script>';
            exit();
        }
    }

    public function setauth()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty($cookie) ? session('user.role_id') : $cookie['role_id'];
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以分配权限!");window.history.back();</script>';
            exit();
        }else{
            $role = \app\admin\model\RoleModel::where('id', $id)->find();
            $top_auth = \app\admin\model\AuthModel::where('pid', 0)->select();
            $second_auth = \app\admin\model\AuthModel::where('pid', '>', 0)->select();
            $this->assign('role', $role);
            $this->assign('top_auth', $top_auth);
            $this->assign('second_auth', $second_auth);
            return view();
        }
    }

    public function saveauth()
    {
        $id = $this->request->param('role_id');
        $auth_id = input('post.');
        $ids = $auth_id['id'];
        $role_auth_ids = implode(',', $ids);
        $res = \app\admin\model\RoleModel::update(['role_auth_ids' => $role_auth_ids],['id' => $id]);
        if($res){
            $this->success("分配成功", 'role/index');
        }else{
            echo '<script>alert("分配失败");window.history.back();</script>';
            exit();
        }
    }

    public function update()
    {
        $id  = input('id');
        $cookie = cookie('user');
        $role_id = empty($cookie) ? session('user.role_id') : $cookie['role_id'];
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以分配权限!");window.history.back();</script>';
            exit();
        }else{
            $role = \app\admin\model\RoleModel::where('id', $id)->find();
            $top_auth = \app\admin\model\AuthModel::where('pid', 0)->select();
            $second_auth = \app\admin\model\AuthModel::where('pid', '>', 0)->select();
            $this->assign('role', $role);
            $this->assign('top_auth', $top_auth);
            $this->assign('second_auth', $second_auth);
            return view();
        }
    }

    public function doupdate()
    {
        $id = $this->request->param('role_id');
        $result = input('post.');
        $ids = $result['id'];
        $role_name = $result['role_name'];
        $role_auth_ids = implode(',', $ids);
        $updated_at = date('Y-m-d H:i:s',time());
        $res = \app\admin\model\RoleModel::update(['role_auth_ids' => $role_auth_ids,'role_name' => $role_name, 'updated_at' => $updated_at],['id' => $id]);
        if($res){
            $this->success("修改成功", 'role/index');
        }else{
            echo '<script>alert("修改失败");window.history.back();</script>';
            exit();
        }
    }

    public function clearallauth()
    {
        $id = input('id');
        $res = \app\admin\Model\RoleModel::where('id', $id)->update(['role_auth_ids' => null]);
        if($res){
            echo '<script>alert("清除成功");window.history.back();</script>';
            exit();
        }else{
            echo '<script>alert("清除失败");window.history.back();</script>';
            exit();
        }
    }
}