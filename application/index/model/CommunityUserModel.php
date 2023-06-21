<?php

namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class CommunityUserModel extends Model
{
    use SoftDelete;
    protected $id = 'id';
    protected $table = 'community_user';
    protected $deleteTime = 'deleted_at';

}