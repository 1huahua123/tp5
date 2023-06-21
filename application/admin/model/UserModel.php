<?php
namespace app\admin\Model;

use think\Model;
use traits\model\SoftDelete;

class UserModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'admin_user';

    public function role()
    {
        return $this->belongsTo('RoleModel', 'role_id', 'id');
    }

}