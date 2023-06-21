<?php
namespace app\admin\Model;

use think\Model;
use traits\model\SoftDelete;

class RoleModel extends Model
{   
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'admin_role';

}