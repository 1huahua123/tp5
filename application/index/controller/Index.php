<?php
namespace app\index\controller;

use app\index\Model\CommentModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\response\Redirect;
use think\Validate;
use phpmailer\PHPMailer;
use think\cache\driver\Redis;
use think\Cookie;

class Index extends Common
{
    /**
     * 网站主页
     * @return array $blog 随机7条资讯
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $redis = new Redis();
        if(empty($redis->get('blog'))){
            $blog = \app\index\model\BlogModel::where('blog_state',1)->orderRaw('rand()')->limit(7)->select();
            $redis->set('blog', $blog, 60);
        }else{
            $blog = $redis->get('blog');
        }
        if(empty($redis->get('hot'))){
            $hot = \app\index\model\BlogModel::with('plate')->where('blog_state',1)->orderRaw('rand()')->limit(1)->find();
            $redis->set('hot', $hot, 60);
        }else{
            $hot = $redis->get('hot');
        }
        if(empty($redis->get('hot_discussion'))){
            $hot_discussion = \app\index\model\BlogModel::with('plate')->where('blog_state',1)->order('blog_comments desc')->limit(3)->select();
            $redis->set('hot_discussion', $hot_discussion, 60);
        }else{
            $hot_discussion = $redis->get('hot_discussion');
        }
        if(empty($redis->get('featured'))){
            $featured = \app\index\model\BlogModel::with('plate')->where('blog_state', 1)->order('blog_likes desc,blog_views desc,blog_comments desc')->limit(5)->select();
            $redis->set('featured', $featured, 60);
        }else{
            $featured = $redis->get('featured');
        }
        if(empty($redis->get('most_views'))){
            $most_views = \app\index\model\BlogModel::with('plate')->where('blog_state', 1)->order('blog_likes desc')->limit(5)->select();
            $redis->set('most_views', $most_views, 60);
        }else{
            $most_views = $redis->get('most_views');
        }
        //精选资讯 查看最多的2条除去最多5条资讯
        if(empty($redis->get('most_views_two'))){
            $most_views_two = \app\index\model\BlogModel::with('plate')->where('blog_state', 1)->order('blog_likes desc')->limit(7)->select();
            $most_views_two = array_splice($most_views_two, 5, 2);
            $redis->set('most_views_two', $most_views_two, 60);
        }else{
            $most_views_two = $redis->get('most_views_two');
        }
        //查询所有资讯的数量
        $all_blog = \app\index\model\BlogModel::where('blog_state', 1)->select();
        $all_blog_count = count($all_blog);
        //查询所有的用户数量
        $all_users = \app\index\model\UserModel::where('state', 1)->select();
        $all_users_count = count($all_users);
        //查询最新的5条资讯
        if(empty($redis->get('latest_blog'))){
            $latest_blog = \app\index\model\BlogModel::with('plate')->where('blog_state', 1)->order('created_at desc')->limit(5)->select();
            $redis->set('latest_blog', $latest_blog, 60);
        }else{
            $latest_blog = $redis->get('latest_blog');
        }
        //查询最新的4条资讯除去最后5条
        if(empty($redis->get('latest_blog_four'))){
            $latest_blog_four = \app\index\model\BlogModel::with('plate,user')->where('blog_state', 1)->order('created_at desc')->limit(14)->select();
            $latest_blog_four = array_splice($latest_blog_four, 4,9);
            $redis->set('latest_blog_four', $latest_blog_four, 60);
        }else{
            $latest_blog_four = $redis->get('latest_blog_four');
        }
        //火热游戏
        if(empty($redis->get('hot_game_plate'))){
            $hot_game_plate = \app\index\model\PlateModel::order('followers desc')->limit(5)->select();
            $redis->set('hot_game_plate', $hot_game_plate, 60);
        }else{
            $hot_game_plate = $redis->get('hot_game_plate');
        }
        
        $this->assign('hot_game_plate', $hot_game_plate);
        $this->assign('latest_blog_four', $latest_blog_four);
        $this->assign('latest_blog', $latest_blog);
        $this->assign('most_views_two', $most_views_two);
        $this->assign('all_users_count', $all_users_count);
        $this->assign('all_blog_count', $all_blog_count);
        $this->assign('most_views', $most_views);
        $this->assign('featured', $featured);
        $this->assign('hot_discussion',$hot_discussion);
        $this->assign('hot', $hot);
        $this->assign('blog', $blog);
        return view();
    }

    /**
     * 创建资讯详情页面资源，进入详情页面自动增加views字段
     * @return array $blog 资讯ID为$id并且状态为正常的资讯内容详情
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function detail()
    {
        $id = input('id');
        //查询id为$id的详细信息
        $blog = \app\index\model\BlogModel::with('plate,user')->where('id',$id)->where('blog_state',1)->find();
        //判断资讯的状态是否为正常
        if($blog['blog_state'] != 1){
            echo "<script>alert('资讯丢失');window.history.back();</script>";
            exit();
        }
        //为内容转译
        $blog['blog_content'] = htmlspecialchars_decode($blog['blog_content']);
        //该资讯views+1
        $blog_views['blog_views'] = $blog['blog_views'] + 1;
        //更改该资讯的views
        \app\index\model\BlogModel::where('id', $id)->where('blog_state',1)->update(['blog_views' => $blog_views['blog_views']]);
        //查询该资讯的上一篇资讯
        $previous = \app\index\model\BlogModel::with('plate')->where('blog_state',1)->where('id','<',$id)->order('id desc')->limit(1)->find();
        //查询该资讯的下一篇资讯
        $next = \app\index\model\BlogModel::with('plate')->where('blog_state',1)->where('id','>',$id)->limit(1)->find();
        //查询该板块除了本篇资讯的任意两篇资讯
        $related = \app\index\model\BlogModel::with('plate')->where('blog_state',1)->where('plate_id',$blog['plate_id'])->where('id','<>',$id)->orderRaw('rand()')->limit(2)->select();
        //查询该资讯的作者信息
        $all_blog_released_by_author = \app\index\model\BlogModel::where('blog_state', 1)->where('blog_author', $blog['blog_author'])->select();
        //获取该作者的发布的所有资讯数量
        $all_blog_released_by_author_count = count($all_blog_released_by_author);
        //查询该资讯的评论
        // //顶层评论
        $top_comment = \app\index\model\CommentModel::with('user')->where('blog_id', $id)->where('leval', 0)->where('state', 1)->select();
        // //下层评论
        $second_comment = \app\index\model\CommentModel::with('user')->where('blog_id', $id)->where('leval', 1)->where('state', 1)->select();
        //搜索该资讯该用户是否收藏
        $cookie = cookie('userinfo');
        if(empty($cookie)){
            $session = session('userinfo');
            $user_id = $session['id'];
            $collect_blog = \app\index\model\CollectModel::where('blog_id', $id)->where('user_id', $user_id)->find();
            if(empty($collect_blog)){
                $this->assign('collect_blog', '');
            }else{
                $this->assign('collect_blog', $collect_blog);
            }
        }else{
            $user_id = $cookie['id'];
            $collect_blog = \app\index\model\CollectModel::where('blog_id', $id)->where('user_id', $user_id)->find();
            if(empty($collect_blog)){
                $this->assign('collect_blog', '');
            }else{
                $this->assign('collect_blog', $collect_blog);
            }
        }
        $this->assign('top_comment', $top_comment);
        $this->assign('second_comment', $second_comment);
        $this->assign('all_blog_released_by_author_count', $all_blog_released_by_author_count);
        $this->assign('related', $related);
        $this->assign('previous', $previous);
        $this->assign('next', $next);
        $this->assign('blog',$blog);
        return view();
    }

    /**
     * 用户对资讯进行点赞的操作
     * @return Redirect|void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function dianzan()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登陆过后再进行点赞");window.history.back();</script>';
            exit();
        }else{
            $id = input('id');
            $blog = \app\index\model\BlogModel::where('id',$id)->where('blog_state',1)->find();
            $blog_likes['blog_likes'] = $blog['blog_likes'] + 1;
            \app\index\model\BlogModel::where('id', $id)->where('blog_state',1)->update(['blog_likes' => $blog_likes['blog_likes']]);
            return redirect('index/detail',['id'=>$id]);
        }
    }

    /**
     * 所有的游戏板块信息
     * @return array $plate
     * @throws DbException
     */
    public function plate_detail()
    {
        $plate = \app\index\model\PlateModel::with('user,blog')->paginate(5);
        $this->assign('data', $plate); 
        return view();
    }

    /**
     * 该板块的所有资讯信息
     * @param int $id 板块ID
     * @return array $data 板块ID为$id的所有资讯，一页显示10条资讯
     * @return array $plate_name 板块ID为$id的板块信息
     * @return array $all_data 板块ID为$id的所有资讯信息
     * @return array $data_count 板块ID为$id的资讯总数
     * @return array $new_one 板块ID为$id的最新一条资讯
     * @return array $new_three 板块ID为$id的最新三条资讯除去最新的一条
     * @return array $hot_game 关注最多的5个游戏板块
     * @return array $most_blog_views_five 板块ID为$id查看数最多的5条资讯
     */
    public function blog_list()
    {
        $id = input('id');
        $data = \app\index\model\BlogModel::where('plate_id',$id)->where('blog_state',1)->order('created_at desc')->paginate(18);
        $plate_name = \app\index\model\PlateModel::where('id', $id)->find();
        //查询该板块所有的资讯
        $all_data = \app\index\model\BlogModel::where('plate_id', $id)->where('blog_state', 1)->select();
        //获取该板块所有资讯的总数
        $data_count = count($all_data);
        //最新的一条资讯
        $new_one = \app\index\model\BlogModel::where('plate_id',$id)->where('blog_state',1)->order('created_at desc')->limit(1)->find();
        //查询最新的倒数3条资讯除去最新的一条
        $new_three = \app\index\model\BlogModel::where('plate_id', $id)->where('blog_state', 1)->where('id','<>',$new_one['id'])->order('created_at desc')->limit(3)->select();
        //查询最火的游戏板块
        $hot_game = \app\index\model\PlateModel::order('followers desc')->limit(5)->select();
        //查询查看最多的5条资讯
        $most_blog_views_five = \app\index\model\BlogModel::where('plate_id',$id)->where('blog_state',1)->order('blog_views desc')->limit(5)->select();
        $this->assign('most_blog_views_five', $most_blog_views_five);
        $this->assign('hot_game', $hot_game);
        $this->assign('new_three', $new_three);
        $this->assign('new_one', $new_one);
        $this->assign('data_count', $data_count);
        $this->assign('plate_name', $plate_name);
        $this->assign('data', $data);
        return view();
    }

    public function collect()
    {
        $blog_id = input('id');
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录");window.history.back();</script>';
            exit();
        }else{
            if(empty(cookie('userinfo'))){
                $session = session('userinfo');
                $user_id = $session['id'];
                $res = \app\index\model\CollectModel::insert(['user_id' => $user_id, 'blog_id' => $blog_id]);
                $blog = \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->find();
                $blog_collects = $blog['blog_collects'] + 1;
                \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->update(['blog_collects' => $blog_collects], true);
                if($res){
                    echo '<script>window.history.back();</script>';
                    exit();
                }else{
                    echo '<script>window.history.back();</script>';
                    exit();
                }
            }else{
                $cookie = cookie('userinfo');
                $user_id = $cookie['id'];
                $res = \app\index\model\CollectModel::insert(['user_id' => $user_id, 'blog_id' => $blog_id]);
                $blog = \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->find();
                $blog_collects = $blog['blog_collects'] + 1;
                \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->update(['blog_collects' => $blog_collects], true);
                if($res){
                    echo '<script>window.history.back();</script>';
                    exit();
                }else{
                    echo '<script>window.history.back();</script>';
                    exit();
                }
            }
        }
    }

    public function cancel_collect()
    {
        $blog_id = input('id');
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录");window.history.back();</script>';
            exit();
        }else{
            if(empty(cookie('userinfo'))){
                $session = session('userinfo');
                $user_id = $session['id'];
                $res = \app\index\model\CollectModel::where('user_id', $user_id)->where('blog_id', $blog_id)->delete();
                $blog = \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->find();
                $blog_collects = $blog['blog_collects'] - 1;
                \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->update(['blog_collects' => $blog_collects], true);
                if($res){
                    echo '<script>window.history.back();</script>';
                    exit();
                }else{
                    echo '<script>window.history.back();</script>';
                    exit();
                }
            }else{
                $cookie = cookie('userinfo');
                $user_id = $cookie['id'];
                $res = \app\index\model\CollectModel::where('user_id', $user_id)->where('blog_id', $blog_id)->delete();
                $blog = \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->find();
                $blog_collects = $blog['blog_collects'] - 1;
                \app\index\model\BlogModel::where('blog_state', 1)->where('id', $blog_id)->update(['blog_collects' => $blog_collects], true);
                if($res){
                    echo '<script>window.history.back();</script>';
                    exit();
                }else{
                    echo '<script>window.history.back();</script>';
                    exit();
                }
            }
        }
    }

    public function usercollect()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("无法访问");window.history.back();</script>';
            exit();
        }
        $user_id = input('id');
        if(empty(session('userinfo'))){
            $cookie = cookie('userinfo');
            if($user_id != $cookie['id']){
                echo '<script>alert("无法访问");window.history.back();</script>';
                exit();
            }
        }else if($user_id != session('userinfo.id')){
            echo '<script>alert("无法访问");window.history.back();</script>';
            exit();
        }
        $data = \app\index\model\CollectModel::with('blog')->where('user_id', $user_id)->order('created_at desc')->paginate(18);
        $this->assign('data', $data);
        return view();
    }

    /**
     * 用户关注板块
     */
    public function follower_plate()
    {
        if(empty(session('user')) && empty(cookie('user'))){
            echo '<script>alert("请登录");window.history.back();</script>';
            exit();
        }
        
    }

    /**
     * 用户申请板块管理员
     */
    public function apply_To_Become_A_Section_Administrator()
    {
        if(empty(session('userinfo')) && empty(cookie('userinfo'))){
            echo '<script>alert("请登录");window.history.back();</script>';
            exit();
        }
        $cookie = cookie('userinfo');
        $user_id = empty(session('userinfo')) ? $cookie['id'] : session('userinfo.id');
        $result = input('post.');
        $result['user_id'] = $user_id;
        $rule = [
            'user_id' => 'require',
            'plate_name' => 'require',
            'application_reason' => 'require'
        ];
        $msg = [
            'user_id.require' => '请登录',
            'plate_name.require' => '请输入想申请的板块名称',
            'application_reason.require' => '请输入申请理由'
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($result)){
            $error = $validate->getError();
            $this->error($error);
        }
        $result['created_at'] = time();
        $result['state'] = "审核中";
        $res = \app\index\model\ApplyToBecomeASectionAdministratorModel::create($result,true);
        if($res){
            $this->success('申请成功，请等待3-7个工作日');
        }else{
            $this->error('申请失败，请再次申请');
        }
    }
}
