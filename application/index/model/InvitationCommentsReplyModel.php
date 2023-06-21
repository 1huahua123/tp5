<?php

namespace app\index\model;

class InvitationCommentsReplyModel extends \think\Model
{
    protected $id = 'id';
    protected $table = 'invitation_reply';

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }

    public function replyUser()
    {
        return $this->belongsTo('UserModel', 'reply_id', 'id');
    }
}