<?php
namespace app\index\Model;

use think\Model;
use traits\model\SoftDelete;

class UserModel extends Model
{   
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'admin_user';

    public function plate()
    {
        return $this->belongsTo('PlateModel', 'plate_id', 'id');
    }

    public function blog()
    {
        return $this->belongsTo('BlogModel', 'blog_author', 'id');
    }
}