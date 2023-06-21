<?php
namespace app\admin\Controller;

use think\Validate;

class Community extends Common
{
    public function index()
    {
        $community = \app\admin\model\CommunityModel::with('user')->select();
        $this->assign('community', $community);
        return view();
    }

    public function create()
    {
        return view();
    }

    public function create_do()
    {
        $result = input('post.');
        $rule = [
            'community_name' => 'require|unique:community',
            'community_profile' => 'require',
        ];
        $msg = [
            'community_name.require' => '请输入社区名称',
            'community_name.unique' => '已经存在该社区',
            'community_profile.require' => '请输入社区简介',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $result['created_at'] = date('Y-m-d H:i:s',time());
        if(empty(session('user'))){
            $cookie = cookie('user');
            $result['community_admin'] = $cookie['id'];
        }else{
            $result['community_admin'] = session('user.id');
        }
        $res = \app\admin\model\CommunityModel::create($result, true);
        if(!$res){
            // echo '<script>alert("创建失败");window.history.back();</script>';
            // exit();
            return $this->error("创建失败");
        }else{
            // echo '<script>alert("创建成功");window.history.back();</script>';
            // exit();
            return $this->success("创建成功",'community/index');
        }
    }

    public function update()
    {
        $id = input('id');
        $data = \app\admin\model\CommunityModel::where('id', $id)->find();
        $this->assign('data', $data);
        return view();
    }

    public function update_do()
    {
        $result = input('post.');
        $rule = [
            'community_name' => 'require|unique:community',
            'community_profile' => 'require',
        ];
        $msg = [
            'community_name.require' => '请输入社区名称',
            'community_name.unique' => '已经存在该社区',
            'community_profile.require' => '请输入社区简介',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        unset($result['uploadimg']);
        $result['updated_at'] = date('Y-m-d H:i:s',time());
        $res = \app\admin\model\CommunityModel::update($result, ['id' => $result['id']]);
        if(!$res){
            // echo '<script>alert("创建失败");window.history.back();</script>';
            // exit();
            return $this->error("修改失败");
        }else{
            // echo '<script>alert("创建成功");window.history.back();</script>';
            // exit();
            return $this->success("修改成功",'community/index');
        }
    }

    public function delete()
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("非法输入");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\CommunityModel::destroy($id);
        if(!$res){
            echo '<script>alert("删除失败");window.history.back();</script>';
            exit();
        }else{
            echo '<script>alert("删除成功");window.history.back();</script>';
            exit();
        }
    }
}