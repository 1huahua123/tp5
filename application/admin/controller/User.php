<?php
namespace app\admin\Controller;

use app\admin\controller\Common;
use think\Request;
use think\Validate;

class User extends Common
{
    public function index()
    {
        $result = \app\admin\model\UserModel::with('role')->where('role_id','<>',5)->paginate(10);
        $this->assign('data', $result);
        return view();
    }

    public function create()
    {
        $role = \app\admin\model\RoleModel::select();
        $this->assign('role', $role);
        return view();
    }

    public function docreate()
    {
        $result = input('post.');
        if(!empty($result)){
            $arr = array();
            $rule = [
                'avatar' => 'require',
                'thumb_avatar' => 'require',
                'username'  => 'require|max:25',
                'account'   => 'require|unique:admin_user|max:11|min:6',
                'password' => 'require|max:15|min:6',
                'pass' => 'require',
                'email' => 'require|email|unique:admin_user',
                'role_id' => 'require',
                'gender' => 'require',
                'state' => 'require',
            ];
            $msg = [
                'avatar.require' => '请上传头像',
                'thumb_avatar' => '请上传头像',
                'username.require' => '用户名不能为空',
                'username.max' => '用户名不能超过25个字符',
                'account.require' => '账号不能为空',
                'account.unique' => '账号已经被注册',
                'account.max' => '账号不能超过11位',
                'account.min' => '账号不能少于6位',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过25个字符',
                'paaword.min' => '密码不能少于6个字符',
                'pass.require' => '重复密码不能为空',
                'email' => '邮箱格式不对',
                'email.unique' => '邮箱已经被注册',
                'role_id.require' => '请选择角色',
                'gender.require' => '请选择性别',
                'state.require' => '请选择状态',
            ];
            $validate = new Validate($rule,$msg);
            if(!$validate->check($result)){
                $error = $validate->getError();
                $this->error($error);
            }
            if($result['password'] != $result['pass']){
                echo '<script>alert("两次输入的密码不一致");window.history.back();</script>';
                exit();
            }
            $arr['username'] = $result['username'];
            $arr['account'] = $result['account'];
            $arr['password'] = md5(md5($result['password']));
            $arr['avatar'] = $result['avatar'];
            $arr['thumb_avatar'] = $result['thumb_avatar'];
            $arr['email'] = $result['email'];
            $arr['role_id'] = $result['role_id'];
            $arr['gender'] = $result['gender'];
            $arr['state'] = $result['state'];
            $cookie = cookie('user');
            $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
            if($arr['role_id'] == 1 && $role_id != 1){
                echo '<script>alert("超级管理员只有超级管理员可以设置");window.history.back();</script>';
                exit();
            }
            $res = \app\admin\model\UserModel::create($arr, true);
            if($res){
                $this->success("添加成功",'user/index');
            }else{
                echo '<script>alert("添加失败");window.history.back();</script>';
                exit();
            }
        }else{
            echo '<script>alert("添加失败");window.history.back();</script>';
            exit();
        }
    }

    public function setpassword()
    {
        $cookie = cookie('user');
        $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以重置密码!");window.history.back();</script>';
            exit();
        }else{
            $password = 666666;
            $password = md5(md5($password));
            $id = input('id');
            $res = \app\admin\model\UserModel::update(['password' => $password], ['id' => $id]);
            if($res){
                $this->success("重置成功",'user/index');
            }else{
                echo '<script>alert("重置失败");window.history.back();</script>';
                exit();
            }
        }
    }

    public function update()
    {
        $id = input('id');
        $user = \app\admin\model\UserModel::where(['id' => $id])->find();
        $role = \app\admin\model\RoleModel::select();
        $this->assign('role', $role);
        $this->assign('user', $user);
        return view();
    }

    public function doupdate()
    {
        $result = input('post.');
        if(!empty($result)){
            $rule = [
                'username'  => 'max:25',
                'account'   => 'unique:admin_user|max:11|min:6',
                'email' => 'email|unique:admin_user',
            ];
            $msg = [
                'username.max' => '用户名不能超过25个字符',
                'account.unique' => '账号已经被注册',
                'account.max' => '账号不能超过11位',
                'account.min' => '账号不能少于6位',
                'email' => '邮箱格式不对',
                'email.unique' => '邮箱已经被注册',
            ];
            $validate = new Validate($rule,$msg);
            if(!$validate->check($result)){
                $error = $validate->getError();
                $this->error($error);
            }
            $updated_at = date('Y-m-d H:i:s',time());
            $result['updated_at'] = $updated_at;
            $cookie = cookie('user');
            $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
            if($arr['role_id'] = 1 && $role_id != 1){
                echo '<script>alert("超级管理员只有超级管理员可以设置");window.history.back();</script>';
                exit();
            }
            $res = \app\admin\model\UserModel::where('id', $result['id'])->update($result,true);
            if($res){
                $this->success("修改成功",'user/index');
            }else{
                echo '<script>alert("修改失败");window.history.back();</script>';
                exit();
            }
        }
    }

    public function delete()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以删除!");window.history.back();</script>';
            exit();
        }else{
            \app\admin\model\UserModel::destroy($id);
            echo '<script>alert("删除成功!");window.history.back();</script>';
            exit();
        }
    }

    public function disable()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以禁用");window.history.back();</script>';
            exit();
        }else{
            $user = \app\admin\model\UserModel::where('id', $id)->find();
            if($user['role_id'] == 1){
                echo '<script>alert("超级管理员无法禁用!!!");window.history.back();</script>';
                exit();
            }else{
                \app\admin\model\UserModel::where('id', $id)->update(['state' => 2]);
                echo '<script>alert("禁用成功");window.history.back();</script>';
                exit();
            }
        }
    }

    public function enable()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty(session('user.role_id')) ? $cookie['role_id'] : session('user.role_id');
        if($role_id != 1){
            echo '<script>alert("只有超级管理员可以禁用");window.history.back();</script>';
            exit();
        }else{
            $user = \app\admin\model\UserModel::where('id', $id)->find();
            if($user['role_id'] == 1){
                echo '<script>alert("超级管理员无法禁用!!!");window.history.back();</script>';
                exit();
            }else{
                \app\admin\model\UserModel::where('id', $id)->update(['state' => 1]);
                echo '<script>alert("解除禁用");window.history.back();</script>';
                exit();
            }
        }
    }

    public function profile()
    {
        $cookie = cookie('user');
        $user_id = empty($cookie) ? session('user.id') : $cookie['id'];
        $user_data = \app\admin\model\UserModel::where('id', $user_id)->find();
        $this->assign('user_data', $user_data);
        $user_blog_nums = \app\admin\model\BlogModel::where('blog_author', $user_id)->count();
        $this->assign('user_blog_nums', $user_blog_nums);
        $user_plate = \app\admin\model\PlateModel::where('plate_admin', $user_id)->select();
        $this->assign('user_plate', $user_plate);
        $user_blog_data = \app\admin\model\BlogModel::where('blog_author', $user_id)
            ->order('created_at desc')
            ->limit(5)
            ->select();
        $this->assign('user_blog_data', $user_blog_data);
        $comments_data = \app\admin\model\CommentModel::with('blog,user')
            ->order('created_at desc')
            ->paginate(8);
        $fenye = $comments_data->render();
        $this->assign('fenye', $fenye);
        $this->assign('comments_data', $comments_data);
        return view();
    }
}