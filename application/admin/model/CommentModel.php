<?php
namespace app\admin\Model;

use think\Model;

class CommentModel extends Model
{
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


}