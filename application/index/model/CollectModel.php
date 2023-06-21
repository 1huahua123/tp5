<?php
namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class CollectModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'collect_blogs';

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }

    public function blog()
    {
        return $this->belongsTo('BlogModel', 'blog_id', 'id');
    }
}