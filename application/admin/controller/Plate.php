<?php
namespace app\admin\Controller;

use think\Validate;

class Plate extends Common
{
    /**
     * 板块列表
     * @return array $result
     */
    public function index()
    {
        $result = \app\admin\model\PlateModel::with('user')->paginate(10);
        $this->assign('data', $result);
        return view();
    }

    /**
     * 创建添加板块视图资源
     * @return view()
     */
    public function create()
    {
        return view();
    }

    /**
     * 添加板块操作
     * @param array $result
     */
    public function docreate()
    {
        $result = input('post.');
        $rule = [
            'plate_name' => 'require|unique:game_plate',
            'plate_avatar' => 'require',
            'plate_profile' => 'require',
        ];
        $msg = [
            'plate_name.require' => '请输入板块名称',
            'plate_name.unique' => '已经存在此板块',
            'plate_avatar.require' => '请上传封面',
            'plate_profile.require' => '请输入板块简介',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $res = \app\admin\model\PlateModel::create(['plate_name' => $result['plate_name'], 'plate_avatar' => $result['plate_avatar'], 'plate_thumb_avatar' => $result['plate_thumb_avatar'], 'plate_profile' => $result['plate_profile']]);
        if($res){
            $this->success("添加成功",'plate/index');
        }else{
            echo '<script>alert("添加失败");window.history.back();</script>';
            exit();
        }
    }

    /**
     * 创建修改板块视图资源
     * @param int $id
     * @return array $plate
     */
    public function update()
    {
        $id = input('id');
        $plate = \app\admin\model\PlateModel::with('user')->where(['id' => $id])->find();
        $user = \app\admin\model\UserModel::where('role_id', 6)->where('id','<>',$plate['plate_admin'])->select();
        $this->assign('user', $user);
        $this->assign('plate', $plate);
        return view();
    }

    /**
     * 修改板块操作
     * @param array $result
     */
    public function doupdate()
    {
        $result = input('post.');
        $rule = [
            'plate_name' => 'require|unique:game_plate',
            'plate_avatar' => 'require',
            'plate_profile' => 'require',
        ];
        $msg = [
            'plate_name.require' => '请输入板块名称',
            'plate_name.unique' => '已经存在此板块',
            'plate_avatar.require' => '请上传封面',
            'plate_profile.require' => '请输入板块简介',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        unset($result['uploadimg']);
        $updated_at = date('Y-m-d H:i:s',time());
        $result['updated_at'] = $updated_at;
        $res = \app\admin\model\PlateModel::where('id', $result['id'])->update($result, true);
        if($res){
            $this->success("修改成功",'plate/index');
        }else{
            echo '<script>alert("修改失败");window.history.back();</script>';
            exit();
        }
    }

    /**
     * 删除板块
     * @param int $id
     */
    public function delete()
    {
        $id = input('id');
        $role_id = session('user.role_id');
        if(empty(session('user'))){
            $cookie = cookie('user');
            $role_id = $cookie['role_id'];
        }
        if($role_id != 1 && $role_id != 4){
            echo '<script>alert("禁止删除");window.history.back();</script>';
            exit();
        }else{
            \app\admin\model\PlateModel::destroy($id);
            $role_id = session('user.role_id');
            echo '<script>alert("删除成功!");window.history.back();</script>';
            exit();
        }
    }

    /**
     * 创建分配板块管理员视图
     */
    public function assign_admin()
    {
        $id = input('id');
        $plate = \app\admin\model\PlateModel::where('id', $id)->find();
        $user = \app\admin\model\UserModel::where('role_id', 6)->select();
        $this->assign('user', $user);
        $this->assign('plate', $plate);
        return view();
    }

    public function doassign_admin()
    {
        $result = input('post.');
        $updated_at = date('Y-m-d H:i:s',time());
        $res = \app\admin\model\PlateModel::where('id', $result['plate_id'])->update(['plate_admin' => $result['plate_admin'], 'updated_at' => $updated_at]);
        if($res){
            $this->success("分配成功", 'plate/index');
        }else{
            echo '<script>alert("分配失败");window.history.back();</script>';
            exit();
        }
    }

    public function detail()
    {
        $id = input('id');
        $data = \app\admin\model\BlogModel::with('user,plate')->where('plate_id',$id)->paginate(7);
        $this->assign('data', $data);
        return view();
    }

    /**
     * 板块管理员审核列表
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function review_Section_Administrator_List()
    {
        $data = \app\admin\model\ApplyToBecomeASectionAdministratorModel::with('user')
            ->paginate(10);
        $this->assign('data', $data);
        return view('plate/review_section_administrator_list');
    }

    public function pass()
    {
        $id = input('id');
        if(empty($id)){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('user');
        $role_id = empty(session('user')) ? $cookie['role_id'] : session('user.role_id');
        if($role_id == 1 || $role_id == 4){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $data = \app\admin\model\ApplyToBecomeASectionAdministratorModel::where('id', $id)->find();
        if(empty($data)){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $user_id = $data['user_id'];
        $user_data = \app\admin\model\UserModel::where('id', $user_id)->find();
        $user_role_id = $user_data['role_id'];
        if($user_role_id == 1 || $user_role_id == 4){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\UserModel::where('id', $user_id)->update(['role_id' => 4]);
        if($res){
            $this->success("审核成功");
        }else{
            $this->error("审核失败");
        }
    }

    public function no_pass()
    {
        $id = input('id');
        if(empty($id)){
            echo '<script>alert("非法操作");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\ApplyToBecomeASectionAdministratorModel::where('id', $id)->update(['state' => "不通过"]);
        if($res){
            $this->success("审核成功");
        }else{
            $this->error("审核失败");
        }
    }
}