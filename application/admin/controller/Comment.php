<?php
namespace app\admin\Controller;

class Comment extends Common
{
    public function index()
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        $data = \app\admin\model\CommentModel::with('user')->where('blog_id', $id)->select();
        $this->assign('data', $data);
        return view();
    }

    public function delete()
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\CommentModel::where('id', $id)->delete();
        if(!$res){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        echo '<script>alert("删除成功");window.history.back();</script>';
        exit();
    }

    public function disable_comment()
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\CommentModel::where('id', $id)->update(['state' => 2], true);
        if(!$res){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        echo '<script>alert("禁用成功");window.history.back();</script>';
        exit();
    }

    public function disactivate_comment()
    {
        $id = input('id');
        if(!$id){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        $res = \app\admin\model\CommentModel::where('id', $id)->update(['state' => 1], true);
        if(!$res){
            echo '<script>alert("网络出错");window.history.back();</script>';
            exit();
        }
        echo '<script>alert("解除禁用成功");window.history.back();</script>';
        exit();
    }
}