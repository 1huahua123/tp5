<?php

namespace app\index\model;

use think\Model;
class InvitationCommentsModel extends Model
{
    protected $id = 'id';
    protected $table = 'invitation_comments';

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }

}