<?php
/**
 * 公共控制器，主要放公共方法/常量
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;
use app\admin\model\AdminLog;
use auth\Auth;//auth权限类引入
header("content-type:text/html;charset=UTF-8");//防乱码

class Base extends Controller
{
    //定义不需要检测登录和不需要检测权限的路由，注意：这里的路由请一律小写
    protected $exclude = [
        'admin/login/login',//登录
        'admin/base/flushSession',//刷新session保持登录
        'admin/oauths/login',//快捷登录
        'admin/oauths/callback',//快捷登录授权回调地址
    ];
    //定义需要登录但是不需要检测权限的路由,注意：这里的路由请一律小写
    protected $login = [
        'admin/base/upload',//图片上传
        'admin/base/sendEmail',//发送邮件
        'admin/index/index',//后台首页
        'admin/index/desktop',//后台我的桌面
        'admin/index/clear',//清除缓存
        'admin/index/logout',//退出登录
        'admin/base/log',//记录日志
        'admin/user/search',//用户搜索
        'admin/user/indentso',//订单搜索
        'admin/user/payso',//充值记录搜索
        'admin/admin/search',//管理员搜索
        'admin/group/search',//权限搜索
    ];
    /**
     * 初始化
     */
    protected function initialize()
    {
        //查询系统信息
        $system = Db::name('system')->where('id',1)->field('ip')->find();
        //判断允许访问的IP是否为空
        if($system['ip'] !== NULL){
            //查询到的ip字段分割成数组的形式
            $pieces = explode(",", $system['ip']);
            //统计有多少个IP地址,便于for循坏
            $count = count($pieces);
            //获取当前客户端的IP地址
            $ip = Request::ip();
            //要检测的ip拆分成数组
            $check_ip_arr = explode('.',$ip);
            //限制IP
            if(!in_array($ip,$pieces)) {
                foreach ($pieces as $val){
                    //发现有*号替代符
                    if(strpos($val,'*') !== false){
                        $arr = explode('.', $val);
                        //用于记录循环检测中是否有匹配成功的
                        $bl = true;
                        for($i=0;$i<$count;$i++){
                            //不等于*  就要进来检测，如果为*符号替代符就不检查
                            if($arr[$i] !== '*'){
                                if($arr[$i] !== $check_ip_arr[$i]){
                                    $bl = false;
                                    //终止检查本个ip 继续检查下一个ip
                                    break;
                                }
                            }
                        }
                        //如果是true则找到有一个匹配成功的就返回
                        if($bl){
                            die;
                        }
                    }
                }
                header('HTTP/1.1 403 Forbidden');
                die("当前IP地址不可访问!");
            }
        }
        //权限检测, 获取当前模块名
        $module = Request::module();
        // 获取当前访问的控制器
        $controller = Request::controller();
        // 获取当前访问的方法
        $action = Request::action();
        //合并
        $url = strtolower($module.'/'.$controller.'/'.$action);
        //排除不需要检测登录和不需要检测权限的路由
        if (!in_array($url,$this->exclude)){
            //判断是否登录
            if(Session::has('Admin')){
                //当超过900秒不操作时删除当前session返回登录页面
                if (time() - Session::get('Admin.time') >= 9000){
                    //删除当前session
                    Session::delete('Admin');
                    //返回登录页面
                    $this->redirect('Login/login');
                }else{
                    //排除不需要检测权限的路由
                    if (!in_array($url,$this->login)){
                        //ID为1的是超级管理员，无须权限检测
                        if (Session::get('Admin.id') !== 1){
                            // 获取auth实例
                            $auth = new Auth();
                            // 检测权限,使用strtolower全部字母转为小写
                            if(!$auth->check($url,Session::get('Admin.id'))){//规则名称,用户UID
                                //没有操作权限
                                $this->error("无操作权限！");
                            }
                        }
                    }
                    //存当前时间戳
                    $Admin['time'] = time();
                    //存当前管理员ID
                    $Admin['id'] = Session::get('Admin.id');
                    Session::set('Admin',$Admin);
                    $session = Session::get('Admin');
                    return $session;
                }
            }else{
                $this->redirect('Login/login');
                return false;
            }
        }
    }

    /**
     * 刷新session保持登录
     */
    public function flushSession()
    {
        //存当前时间戳加上135分钟
        $Admin['time'] = time() + 8100;
        //存当前管理员ID
        $Admin['id'] = Session::get('Admin.id');
        Session::set('Admin',$Admin);
        $session = Session::get('Admin');
        return $session;
    }

    /**
     * 图片文件上传
     */
    public function upload()
    {
        //接收上传的文件
        $file = request()->file('file');
        //移动到框架指定目录
        $info = $file->validate(['size'=>156780,'ext'=>'jpg,png,gif,ico,bmp'])->move('./uploads');
        if ($info){
            //获取图片名称
            $imgName = str_replace("\\","/",$info->getSaveName());
            $photo = '/uploads/'.$imgName;
        }else{
            $photo = "";
        }
        //判断上传是否成功
        if($photo == ""){
            return ['code'=>0,'msg'=>"上传失败！"];
        }else{
            return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
        }
    }

    /**
     * 发送邮件
     * $title:邮件标题,$content:邮件正文,$name:发送人名称,$email:需要发送邮箱
     */
    public function sendEmail($email,$title,$content)
    {
        //查询邮件配置信息
        $info = Db::name('emial')->where('id',1)->find();
        //查询网站信息
        $system = Db::name('system')->where('id',1)->field('name')->find();
        if($info){
            //执行邮件发送
            $res = sendEmail($info['email'],$info['key'],$info['smtp'],$info['sll'],$system['name'],$title,$content,$email);
            if ($res){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /*
     * 记录管理员日志
     * $content:日志内容,$type日志类型（1为登录日志，2为操作日志）,$id管理员ID
     */
    public function log($content,$type = 2,$id = "")
    {
        //删除大于60天的日志
        Db::name('admin_log')->where('create_time','<= time',time() - (84600*60))->delete();
        //判断登录session是否存在，存在则取当前管理员ID
        if (Session::has('Admin')){
            //获取当前管理员ID
            $id = Session::get('Admin.id');
        }
        //记录当前客户端IP地址
        $ip = Request::ip();
        //实例化对象
        $log = new AdminLog();
        //执行添加并过滤非数据表字段
        $log->allowField(true)->save(['type'=>$type,'admin_id'=>$id,'content'=>$content,'ip'=>$ip]);
    }
}