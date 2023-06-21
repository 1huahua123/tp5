<?php

namespace app\admin\model;

use think\Model;

class ApplyToBecomeASectionAdministratorModel extends Model
{
    protected $id = 'id';
    protected $table = 'apply_to_become_a_section_administrator';

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'id');
    }
}