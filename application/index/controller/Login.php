<?php
namespace app\index\controller;

use think\Validate;
use phpmailer\PHPMailer;
use think\cache\driver\Redis;
use think\Cookie;

class Login extends Common
{
    /**
     * 创建登陆页面资源表单
     */
    public function login()
    {
        return view();
    }

    /**
     * 登陆操作
     * @param array[email,password]
     */
    public function dologin()
    {
        $result = input('post.');
        $rule = [
            'account' => 'require|min:6|max:11',
            'password' => 'require|min:6|max:16',
        ];
        $msg = [
            'account.require' => '请输入账号',
            'account.min' => '账号正确格式为6到11位字符',
            'account.max' => '账号正确格式位6到11位字符',
            'paasword.require' => '请输入密码',
            'password.min:6' => '密码正确格式为6到16位字符',
            'password.max:16' => '密码正确格式为6到16位字符',
        ];
        $validata = new Validate($rule, $msg);
        if(!$validata->check($result)){
            $error = $validata->getError();
            return $this->error($error);
        }
        $res = \app\index\model\UserModel::where(['account' => $result['account'], 'password' => md5(md5($result['password']))])->find();
        if(!$res){
            echo '<script>alert("登陆失败");window.history.back();</script>';
            exit();
        }else{
            if($res['state'] != 1){
                echo '<script>alert("该账号被封禁");window.history.back();</script>';
                exit();
            }else{
                if(array_key_exists("remeber_me",$result) == true){
                    Cookie('userinfo', $res->toArray(), 3600*24*365);
                    session('userinfo', $res->toArray());
                    return $this->redirect('index/index');
                }else{
                    session('userinfo', $res->toArray());
                    return $this->redirect('index/index');
                }
            }
        }
    }

    public function logout()
    {
        session('userinfo', null);
        cookie('userinfo', null);
        $this->redirect('index/index');
    }

    /**
     * QQ邮箱发送6位验证码
     * @param $email
     * @return $code
     */
    public function send_email()
    {
        $email = input('param.email');//收件人邮箱
        $sendmail = '1850529744@qq.com';//发件人邮箱
        $sendmailpswd = "wlleagxeipeqfceb";//客户端授权密码
        $send_name = '游戏网站';//设置发件人信息
        $toemail = $email;//定义收件人的邮箱
        $to_name = 'h1';//设置收件人信息，如邮件格式说明中的收件人
        $mail = new PHPMailer();
        $mail->isSMTP();//使用smtp服务
        $mail->CharSet = 'utf8';//编码格式为utf8
        $mail->Host = "smtp.qq.com";//发送方的服务器地址
        $mail->SMTPAuth = true;//是否使用身份验证
        $mail->Username = $sendmail;//发送方的
        $mail->Password = $sendmailpswd;//客户端授权密码
        $mail->SMTPSecure = 'ssl';//使用ssl协议
        $mail->Port = 465;//qq端口465或587
        $mail->setFrom($sendmail, $send_name);//设置发件人信息，如邮件格式说明中的发件人
        $mail->addAddress($toemail, $to_name);//设置收件人信息，如邮件格式说明中的收件人
        $mail->addReplyTo($sendmail, $send_name);//设置回复人信息
        $mail->Subject = "验证码";
        $code=rand(100000,999999);
        $redis = new Redis();
        $redis->set('code', $code, 60);
        $mail->Body = "您的验证码是：".$code."，如果非本人操作无需理会！";// 邮件正文
        if (!$mail->send()) { // 发送邮件
            return json(['code'=> '201', 'msg'=>$mail->ErrorInfo]);
        }else{
            return json(['code'=>'200','msg'=>'发送验证码成功!']);
        }
    }

    /**
     * 创建注册用户资源表单
     */
    public function regist()
    {
        return view();
    }

    /**
     * 用户注册操作
     * @param array $result
     */
    public function doregist()
    {
        $result = input('post.');
        $rule = [
            'avatar' => 'require',
            'username' => 'require|max:10|min:3',
            'account' => 'require|max:11|unique:admin_user|min:6',
            'email' => 'require|unique:admin_user',
            'code' => 'require|length:6',
            'password' => 'require|max:16|min:8',
            'pass' => 'require|max:16|min:8',
            'gender' => 'require',
            'birthday' => 'require',
        ];
        $msg = [
            'avatar.require' => '请上传头像',
            'username.require' => '请输入用户名',
            'username.max' => '用户名格式应该为3到10位字符',
            'username.min' => '用户名格式应该为3到10位字符',
            'account.require' => '请输入账号',
            'account.max' => '账号格式应该为6到11位字符',
            'account.min' => '账号格式应该为6到11位字符',
            'account.unique' => '该账号已经被使用',
            'email.require' => '请输入邮箱',
            'email.unique' => '该邮箱已经注册',
            'code.require' => '请输入验证码',
            'code.leength' => '请输入6位验证码',
            'password.require' => '请输入密码',
            'password.max' => '请输入正确格式密码',
            'password.min' => '请输入正确格式密码',
            'pass.require' => '请重复输入密码',
            'pass.max' => '请输入正确格式密码',
            'pass.min' => '请输入正确格式密码',
            'gender.require' => '请选择性别',
            'birthday.require' => '请输入您的生日',
        ];
        $validata = new Validate($rule, $msg);
        if(!$validata->check($result)){
            $error = $validata->getError();
            return $this->error($error);
        }
        if($result['password'] != $result['pass']){
            echo '<script>alert("输入的两次密码不一致");window.history.back();</script>';
            exit();
        }
        $redis = new Redis();
        $code = $redis->get('code');
        if(!$code){
            echo '<script>alert("验证码已过期!");window.history.back();</script>';
            exit();
        }else if($code != $result['code']){
            echo '<script>alert("验证码错误!");window.history.back();</script>';
            exit();
        }
        $arr = array();
        $arr['username'] = $result['username'];
        $arr['account'] = $result['account'];
        $arr['password'] = $result['password'];
        $arr['avatar'] = $result['avatar'];
        $arr['thumb_avatar'] = $result['thmb_avatar'];
        $arr['email'] = $result['email'];
        $arr['role_id'] = 5;
        $arr['gender'] = $result['gender'];
        $arr['birthday'] = $result['birthday'];
        $arr['state'] = 1;
        $res = \app\index\model\UserModel::create($arr, true);
        if($res){
            return $this->success("注册成功", 'index/login');
        }else{
            echo '<script>alert("注册失败");window.history.back();</script>';
            exit();
        }
    }

    public function forgot_password()
    {
        $result = input('post.');
        if(!$result){
            return view();
        }
        $rule = [
            'email' => 'require',
            'code' => 'require',
            'password' => 'require|max:16|min:8',
        ];
        $msg = [
            'email.require' => '请输入邮箱',
            'code.require' => '请输入验证码',
            'password.require' => '请输入密码',
            'password.max' => '密码格式应该为8到16位字符',
            'password.min' => '密码格式应该为8到16位字符',
        ];
        $validata = new Validate($rule, $msg);
        if(!$validata->check($result)){
            $error = $validata->getError();
            $this->error($error);
        }
        $email = $result['email'];
        $password = md5(md5($result['password']));
        $redis = new Redis();
        $code = $redis->get('code');
        if($result['code'] == $code){
            $res = \app\index\model\UserModel::where('email', $email)->update('password', $password);
            if($res){
                echo '<script>alert("修改密码成功");window.history.back();</script>';
                exit();
            }else{
                echo '<script>alert("修改密码失败");window.history.back();</script>';
                exit();
            }
        }else{
            echo '<script>alert("请输入正确的验证码");window.history.back();</script>';
            exit();
        }
    }
}