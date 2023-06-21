<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Request;

// 应用公共文件
if(!function_exists('getTree')) {
    //递归方法实现无极限分类
    function getTree($list, $pid = 0, $level = 0) {
        static $tree = array();
        foreach($list as $row){
            if($row['pid'] == $pid){
                $row['level'] = $level;
                $tree[] = $row;
                getTree($list, $row['id'], $level + 1);
            }
        }
        return $tree;
    }
}

function get_ip()
{
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $cip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $cip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $cip = $_SERVER['REMOTE_ADDR'];
    }else{
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);
    return $cip;
}

