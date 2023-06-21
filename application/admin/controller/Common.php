<?php
namespace app\admin\Controller;

use think\Controller;
use think\Cookie;
use think\Request;
use think\Image;

class Common extends Controller
{
    public function _initialize()
    {
        if(empty(session('?user')) && empty(cookie('user'))){
            $this->redirect('login/index');
        }
        $this->header();
        $this->top();
        $this->footer();
        $this->checkauth();
    }

    public function header()
    {
        $session = session('user');
        $cookie = cookie('user');
        if(!empty($cookie)){
            $role_id = $cookie['role_id'];
            $id = $cookie['id'];
            $this->assign('user', $cookie);
        }elseif (!empty($session)) {
            $role_id = session('user.role_id');
            $id = session('user.id');
            $this->assign('user', $session);
        }
        $user = \app\admin\model\UserModel::where('id', $id)->find();
        if($role_id == 1){
            $top_nav = \app\admin\model\AuthModel::where(['pid' => 0, 'is_nav' => 0])->select();
            $second_nav = \app\admin\model\AuthModel::where('pid', '>', '0')->where('is_nav', 0)->select();
        }else{
            //其他人,查询拥有的权限
            $role = \app\admin\model\RoleModel::find($role_id);
            $role_auth_ids = $role->role_auth_ids;
            //在查询权限表
            $top_nav = \app\admin\model\AuthModel::whereIn('id',$role_auth_ids)->where('pid',0)->where('is_nav',0)->select();
            //二级权限
            $second_nav = \app\admin\model\AuthModel::whereIn('id',$role_auth_ids)->where('pid','>',0)->where('is_nav',0)->select();
        }
        $this->assign('top_nav', $top_nav);
        $this->assign('second_nav', $second_nav);
        $this->assign('u',$user);
    }

    public function top()
    {
        $cookie = cookie('user');
        $userinfo = empty($cookie) ? session('user') : $cookie;
        // dump($user);
        // exit();
        $this->assign('userinfo', $userinfo);
        return view();
    }

    public function footer()
    {
        return view();
    }

    public function checkauth()
    {
        $session = session('user');
        $cookie = cookie('user');
        if(!empty($cookie)){
            $role_id = $cookie['role_id'];
        }elseif (!empty($session)) {
            $role_id = session('user.role_id');
        }
        if($role_id == 1){
            return;
        }
        $controller = \request()->controller();
        $action = \request()->action();
        $auth = \app\admin\model\AuthModel::where(['auth_c' => $controller, 'auth_a' => $action])->find();
        $auth_id = $auth['id'];
        $role= \app\admin\model\RoleModel::find($role_id);
        //字符串变数组
        $role_auth_ids = explode(',', $role['role_auth_ids']);
        //判读
        if (!in_array($auth_id,$role_auth_ids)){
            $this->error('权限不足,需要此功能，请联系管理员');
        }
    }

    public function upload(Request $request)
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move('./static/img/avatars');
            if($info){
                //原图
                 $img='/static/img/avatars/' . str_replace('\\', '/', $info->getSaveName());
                //生成缩略图
				$temp = explode(DS,$info->getSaveName());
				$url = DS . 'static/img/avatars' .DS . $temp[0] . DS . 'thumb_150_'.$temp[1];
                $image = Image::open('.'.$img)->thumb(150, 150,\think\Image::THUMB_CENTER)->save('.'.$url);
                $result = [
                    'code'     => 200,
                    'msg'      => '上传成功',
                    'filename' => $img,
                    'thumb'=>$url
                ];
                return json($result);
            }else{
                return json(['code' => 204,'msg'=>'上传失败']);
            }
        }else{
            return json(['code' => 205,'msg'=>'未接收到数据']);
        }
    }

    public function upload_plate_avatar(Request $request)
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move('./static/img/plate/avatars');
            if($info){
                //原图
                 $img='/static/img/plate/avatars/' . str_replace('\\', '/', $info->getSaveName());
                //生成缩略图
				$temp = explode(DS,$info->getSaveName());
				$url = DS . 'static/img/plate/avatars' .DS . $temp[0] . DS . 'thumb_150_'.$temp[1];
                $image = Image::open('.'.$img)->thumb(150, 150,\think\Image::THUMB_CENTER)->save('.'.$url);
                $result = [
                    'code'     => 200,
                    'msg'      => '上传成功',
                    'filename' => $img,
                    'thumb'=>$url
                ];
                return json($result);
            }else{
                return json(['code' => 204,'msg'=>'上传失败']);
            }
        }else{
            return json(['code' => 205,'msg'=>'未接收到数据']);
        }
    }

    public function upload_blog_cover(Request $request)
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move('./static/img/blog/covers');
            if($info){
                //原图
                $img='/static/img/blog/covers/' . str_replace('\\', '/', $info->getSaveName());
                $result = [
                    'code'     => 200,
                    'msg'      => '上传成功',
                    'filename' => $img,
                ];
                return json($result);
            }else{
                return json(['code' => 204,'msg'=>'上传失败']);
            }
        }else{
            return json(['code' => 205,'msg'=>'未接收到数据']);
        }
    }

    public function upload_community_avatar(Request $request)
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move('./static/img/community/avatars');
            if($info){
                $img = '/static/img/community/avatars/' . str_replace('\\', '/', $info->getSaveName());
                $result = [
                    'code' => 200,
                    'msg' => '上传成功',
                    'filename' => $img,
                ];
                return json($result);
            }else{
                return json(['code' => 204, 'msg' => '上传失败']);
            }
        }else{
            return json(['code' => 205, 'msg' => '未接收到数据']);
        }
    }
}