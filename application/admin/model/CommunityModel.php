<?php
namespace app\admin\Model;

use think\Model;
use traits\model\SoftDelete;

class CommunityModel extends Model
{
    use SoftDelete;
    protected $id = 'id';
    protected $table = 'community';
    protected $deleteTime = 'deleted_at';

    public function user()
    {
        return $this->belongsTo('UserModel', 'community_admin', 'id');
    }
}