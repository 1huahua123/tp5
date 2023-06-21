<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;
use traits\model\SoftDelete;

class InvitationCommentsModel extends Model
{
    use SoftDelete;
    protected $id = 'id';
    protected $table = 'invitation_comments';
    protected $deleteTime = 'deleted_at';

    public function user(): BelongsTo
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo('InvitationModel', 'invitation_id', 'id');
    }
}