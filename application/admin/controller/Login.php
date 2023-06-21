<?php
namespace app\admin\Controller;

use think\Controller;
use think\Cookie;
use think\Validate;

class Login extends Controller
{
    public function index()
    {
        return view();
    }

    public function dologin()
    {
        $result = input('post.');
        $rule = [
            'email' => 'email|require',
            'password' => 'require',
        ];
        $msg = [
            'email.require' => '邮箱不能为空',
            'email' => '邮箱格式不对',
            'password.require' => '密码不能为空',
        ];
        $validate = new Validate($rule,$msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $password = md5(md5($result['password']));
        $user = \app\admin\Model\UserModel::where(['email' => $result['email'], 'password' => $password])->find();
        if($user){
            if($user['state'] == 2){
                echo '<script>alert("账号禁用，请联系超级管理员!");window.history.back();</script>';
                exit();
            }
            if(array_key_exists("remember_me",$result) == true){
                cookie('user', $user->toArray(), 3600*24*30);
                session('user', $user->toArray());
                $this->redirect('index/index');
            }else{
                session('user',$user->toArray());
                $this->redirect('index/index');
            }
        }else{
            echo '<script>alert("账号或密码错误!");window.history.back();</script>';
        }
    }

    public function logout()
    {
        session('user', null);
        cookie('user', null);
        return $this->redirect('login/index');
    }

    public function regist()
    {
        return view();
    }

    public function doregist()
    {
        $result = input('post.');
        if(empty($result)){
            echo '<script>alert("注册失败");window.history.back();</script>';
            exit();
        }else{
            $arr = array();
            $rule = [
                'username'  => 'require|max:25',
                'account'   => 'require|unique:admin_user',
                'password' => 'require|max:25',
                'email' => 'require|email|unique:admin_user',
            ];
            $msg = [
                'username.require' => '用户名不能为空',
                'username.max' => '用户名不能超过25个字符',
                'account.require' => '账号不能为空',
                'account.unique' => '账号已经被注册',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过25个字符',
                'email' => '邮箱格式不对',
                'email.unique' => '邮箱已经被注册',
            ];
            $validate = new Validate($rule,$msg);
            if(!$validate->check($result)){
                $error = $validate->getError();
                $this->error($error);
            }
            if($result['password'] != $result['pass']){
                echo '<script>alert("两次密码不一致");window.history.back();</script>';
                exit();
            }
            $arr['username'] = $result['username'];
            $arr['account'] = $result['account'];
            $arr['password'] = md5(md5($result['password']));
            $arr['email'] = $result['email'];
            $arr['gender'] = $result['gender'];
            $res = \app\admin\model\UserModel::insert($arr);
            if($res){
                $this->success('注册成功','login/index');
            }else{
                echo '<script>alert("注册失败");window.history.back();</script>';
                exit();
            }
        }
    }
}