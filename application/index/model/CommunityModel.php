<?php
namespace app\index\Model;

use think\Model;

class CommunityModel extends Model
{
    protected $id = 'id';
    protected $table = 'community';
    
    public function user()
    {
        return $this->belongsTo('UserModel', 'community_admin', 'id');
    }
}