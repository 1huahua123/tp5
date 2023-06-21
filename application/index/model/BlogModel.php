<?php
namespace app\index\Model;

use think\Model;
use traits\model\SoftDelete;

class BlogModel extends Model
{   
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'game_blog';

    public function user()
    {
        return $this->belongsTo('UserModel', 'blog_author', 'id');
    }

    public function plate()
    {
        return $this->belongsTo('PlateModel', 'plate_id', 'id');
    }
}