<?php
namespace app\index\Model;

use think\Model;
use traits\model\SoftDelete;

class CommentModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'game_comments';

    public function blog()
    {
        return $this->belongsTo('BlogModel', 'blog_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }

    public function index()
    {
        $comments = $this->with('user,blog')->where('pid', '=', 0)->order('created_at', 'desc')->select();
        foreach ($comments as $comment) {
            $comment->children;
        }
        return $comments;
    }

    public function children()
    {
        return $this->hasMany('CommentModel', 'pid')->order('created_at', 'desc');
    }
}