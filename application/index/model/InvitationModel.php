<?php

namespace app\index\model;

use think\Model;

class InvitationModel extends Model
{
    protected $id = 'id';
    protected $table = 'community_invitation';

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }
}