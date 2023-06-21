<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class InvitationModel extends Model
{
    use SoftDelete;
    protected $id = 'id';
    protected $table = 'community_invitation';
    protected $deleteTime = 'deleted_at';

    public function user()
    {
        return $this->belongsTo('UserModel','user_id','id');
    }

}