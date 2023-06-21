<?php
namespace app\index\controller;

use app\index\Model\CommentModel;

class Comment extends Common
{
    public function index()
    {
        $model = new CommentModel();
        $comments = $model->index();
        return json(['code' => '0', 'comments' => $comments]);
    }

    /**
     * 顶层评论回复
     */
    public function reply()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录过后再进行评论");window.history.back();</script>';
            exit();
        }else{
            $result = input('post.');
            if(!empty(cookie('userinfo'))){
                $cookie = cookie('userinfo');
                $user_id = $cookie['id'];
                $blog_id = input('id');
                $result['blog_id'] = $blog_id;
                $result['user_id'] = $user_id;
                $result['pid'] = 0;
                $result['leval'] = 0;
                $result['user_ip'] = get_ip();
                $result['state'] = 1;
                $res = \app\index\model\CommentModel::create($result, true);
                if($res){
                    $blog = \app\index\model\BlogModel::where('id', $blog_id)->find();
                    \app\index\model\BlogModel::where('id', $blog_id)->update(['blog_comments' => $blog['blog_comments'] + 1], true);
                    echo '<script>window.history.back();</script>';
                    exit();
                }else{
                    echo '<script>window.history.back();</script>';
                    exit();
                }
            }
        }
    }

    /**
     * 下级评论回复
     */
    public function preply()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录过后再进行评论");window.history.back();</script>';
            exit();
        }else{
            $result = input('post.');
            $blogid = input('id');
            $pid = input('pid');
            dump($pid);
            exit();
            $cookie = cookie('userinfo');
            $user_id = $cookie['id'];
            $blog_id = input('id');
            $pid = input('pid');
            $result['user_id'] = $user_id;
            $result['blog_id'] = $blog_id;
            $result['pid'] = $pid;
            $result['leval'] = 1;
            $user = \app\index\model\CommentModel::where('id', $pid)->find();
            $uid = \app\index\model\UserModel::where('id', $user['id'])->find();
            $puser_name = $uid['username'];
            $result['puser_name'] = $puser_name;
            $res = \app\index\model\CommentModel::create($result, true);
            if($res){
                $blog = \app\index\model\BlogModel::where('id', $blog_id)->find();
                \app\index\model\BlogModel::where('id', $blog_id)->update(['blog_comments' => $blog['blog_comments'] + 1], true);
                echo '<script>window.history.back();</script>';
                exit();
            }else{
                echo '<script>window.history.back();</script>';
                exit();
            }
        }
    }

    /**
     * 评论点赞
     */
    public function like_comment()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录后再尝试");window.history.back();</script>';
            exit();
        }
        $id = input('id');
        $comment = \app\index\model\CommentModel::where('id', $id)->find();
        if($comment){
            $like_num = $comment['like_num'] + 1;
            \app\index\model\CommentModel::where('id', $id)->update(['like_num' => $like_num], true);
            echo '<script>window.history.back();</script>';
            exit();
        }else{
            echo '<script>alert("网络波动请稍微再试");window.history.back();</script>';
            exit();
        }
    }
}