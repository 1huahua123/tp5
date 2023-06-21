<?php
namespace app\index\Model;

use think\Model;
use traits\model\SoftDelete;

class PlateModel extends Model
{   
    use SoftDelete;
    protected $deleteTime = 'deleted_at';
    protected $id = 'id';
    protected $table = 'game_plate';

    public function user()
    {
        return $this->belongsTo('UserModel', 'plate_admin', 'id');
    }

    public function blog()
    {
        return $this->belongsTo('BlogModel', 'id', 'plate_id');
    }
}