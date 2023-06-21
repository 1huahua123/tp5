<?php
namespace app\admin\Controller;

use think\Request;
use think\Validate;

class Blog extends Common
{
    public function index()
    {
        $data = \app\admin\model\BlogModel::with('user,plate')->order("created_at desc")->paginate(7);
        $this->assign('data', $data);
        return view();
    }

    public function create()
    {
        $plate = \app\admin\model\PlateModel::select();
        $this->assign('plate', $plate);
        return view();
    }

    public function docreate()
    {
        $addinfo = input('post.');
        $rule = [
            'blog_title' => 'require',
            'blog_content' => 'require',
            'blog_state' => 'require',
        ];
        $msg = [
            'blog_title.require' => '资讯标题不能为空',
            'blog_content' => '资讯内容不能为空',
            'blog_state.require' => '请选择状态',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($addinfo)){
            $error = $validate->getError();
            return $this->error($error);
        }
        $arr = array();
        $cookie = cookie('user');
        $arr['blog_author'] = empty($cookie) ? session('user.id') : $cookie['id'];
        $arr['blog_title'] = trim($addinfo['blog_title']);
        $arr['blog_cover'] = $addinfo['blog_cover'];
        $arr['blog_content'] = htmlspecialchars($addinfo['blog_content']);
        $arr['plate_id'] = $addinfo['plate_id'];
        $arr['blog_state'] = trim($addinfo['blog_state']);
        $res = \app\admin\model\BlogModel::create($arr, true);
        if($res){
            $this->success("上传成功",'blog/index');
        }else{
            echo '<script>alert("上传失败");window.history.back();</script>';
            exit();
        }
    }

    public function update()
    {
        $id = input('id');
        $blog = \app\admin\model\BlogModel::with('plate')->where('id', $id)->find();
        $plate = \app\admin\model\PlateModel::select();
        $this->assign('blog', $blog);
        $this->assign('plate', $plate);
        return view();
    }

    public function doupdate()
    {
        $result = input('post.');
        $arr = array();
        $updated_at = date('Y-m-d H:i:s',time());
        $result['updated_at'] = $updated_at;
        $res = \app\admin\model\BlogModel::where('id', $result['id'])->update($result, true);
        if($res){
            echo '<script>alert("修改成功");window.history.go(-2);</script>';
            exit();
            // $this->success("修改成功", 'blog/index');
        }else{
            echo '<script>alert("修改失败");window.history.back();</script>';
            exit();
        }
    }

    public function delete()
    {
        $id = input('id');
        $cookie = cookie('user');
        $role_id = empty($cookie) ? session('user.role_id') : $cookie['role_id'];
        if($role_id == 1 || $role_id == 4 || $role_id == 6){
            \app\admin\model\BlogModel::where('id',$id)->update(['blog_state' => 4]);
            \app\admin\model\BlogModel::destroy($id);
            echo '<script>alert("删除成功");window.history.back();</script>';
            exit();
        }else{
            echo '<script>alert("不能删除");window.history.back();</script>';
            exit();
        }
    }

    public function detail()
    {
        $id = input('id');
        $data = \app\admin\model\BlogModel::with('user,plate')->where('id', $id)->find();
        $content = htmlspecialchars_decode($data['blog_content']);
        $this->assign('content', $content);
        $this->assign('data', $data);
        return view();
    }

}