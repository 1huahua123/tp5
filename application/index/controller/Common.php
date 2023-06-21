<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Image;
use phpmailer\PHPMailer;
use think\Validate;

class Common extends Controller
{
    public function _initialize()
    {
        $this->head();
        $this->header();
        $this->foot();
        $this->foot_address();
        $this->foot_message_list();
        $this->foot_board_message();
        $this->foot_videos();
    }

    public function head()
    {
        return view();
    }

    public function header()
    {
        $plate = \app\index\model\PlateModel::select();
        //最新的三条资讯
        $new_blog = \app\index\model\BlogModel::where('blog_state',1)->order('created_at desc')->limit(3)->select();
        //查找session
        $session = session('userinfo');
        $cookie = cookie('userinfo');
        if(!empty($cookie)){
            $this->assign('user', $cookie);
            $this->assign('new_blog', $new_blog);
            $this->assign('plate', $plate);
            return view();
        }elseif (!empty($session)) {
            $this->assign('user', $session);
            $this->assign('new_blog', $new_blog);
            $this->assign('plate', $plate);
            return view();
        }elseif (empty($cookie) && empty($session)) {
            $this->assign('user', '');
            $this->assign('new_blog', $new_blog);
            $this->assign('plate', $plate);
            return view();
        }
    }

    public function foot()
    {
        return view();
    }

    public function foot_address()
    {
        return view();
    }

    public function foot_board_message()
    {
        return view();
    }

    public function foot_videos()
    {
        $foot_videos_data = \app\index\model\BlogModel::order('created_at desc')
            ->order('blog_likes desc')
            ->order('blog_views desc')
            ->order('blog_collects desc')
            ->order('blog_comments desc')
            ->where('blog_state', 1)
            ->limit(2)
            ->select();
        $this->assign('foot_videos_data', $foot_videos_data);
        return view();
    }

    public function foot_message_list()
    {
        $message_data = \app\index\model\BoardModel::order('created_at desc')->select();
        $this->assign('message_data', $message_data);
        return view();
    }

    public function upload(Request $request)
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move('./static/img/avatars');
            if($info){
                //原图
                 $img='/static/img/avatars/' . str_replace('\\', '/', $info->getSaveName());
                //生成缩略图
				$temp = explode(DS,$info->getSaveName());
				$url = DS . 'static/img/avatars' .DS . $temp[0] . DS . 'thumb_150_'.$temp[1];
                $image = Image::open('.'.$img)->thumb(150, 150,\think\Image::THUMB_CENTER)->save('.'.$url);
                $result = [
                    'code'     => 200,
                    'msg'      => '上传成功',
                    'filename' => $img,
                    'thumb'=>$url
                ];
                return json($result);
            }else{
                return json(['code' => 204,'msg'=>'上传失败']);
            }
        }else{
            return json(['code' => 205,'msg'=>'未接收到数据']);
        }
    }

    public function commentlist($blog_id, $pid = 0)
    {
        $commentlist = array();
        $list = \app\index\model\CommentModel::with('user')->where('blog_id', $blog_id)->where('pid', $pid)->select();
        foreach($list as $v){
            $commentlist[] = $v;
            //查询子评论
            $zi = $this->commentlist($blog_id, $v['blog_id']);
            if(count($zi)){
                foreach($zi as $v1){
                    $commentlist[] = $v1;
                }
            }
        }
        return $commentlist;
    }

    public function search()
    {
        $result = input('post.');
        $data = \app\index\model\BlogModel::where('blog_state', 1)->where('blog_title', 'like', '%'.$result['keywords'].'%')->paginate(18);
        $this->assign('keyword', $result['keywords']);
        $this->assign('data', $data);
        return view();
    }

    public function send_board_message()
    {
        $result = input('post.');
        $cookie = cookie('userinfo');
        if(empty($cookie) && empty(session('userinfo'))){
            $this->error('请登录', 'index/Login/login');
        }
        $user_email = empty($cookie) ? session('userinfo.email') : $cookie['email'];
        $result['email'] = $user_email;
        $result['created_at'] = date('Y-m-d H:i:s', time());
        $rule = [
            'message_content' => 'require',
        ];
        $msg = [
            'message_content.require' => '请输入留言内容',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $res = \app\index\model\BoardModel::create($result, true);
        if(!$res){
            echo '<script>alert("网络出错请稍后留言");window,history.bcak();</script>';
            exit();
        }else{
            $this->success('留言成功');
        }
    }
}