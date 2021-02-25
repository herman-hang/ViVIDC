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

class Base extends Controller
{
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
            //统计有多少个IP地址
            $count = count($pieces);
            //将数组$pieces赋值给$ALLOWED_IP
            $ALLOWED_IP=$pieces;
            //获取当前客户端的IP地址
            $IP=Request::ip();
            //要检测的ip拆分成数组
            $check_ip_arr= explode('.',$IP);
            //限制IP
            if(!in_array($IP,$ALLOWED_IP)) {
                foreach ($ALLOWED_IP as $val){
                    //发现有*号替代符
                    if(strpos($val,'*')!==false){
                        $arr=array();
                        $arr=explode('.', $val);
                        //用于记录循环检测中是否有匹配成功的
                        $bl=true;
                        for($i=0;$i<$count;$i++){
                            //不等于*  就要进来检测，如果为*符号替代符就不检查
                            if($arr[$i]!='*'){
                                if($arr[$i]!=$check_ip_arr[$i]){
                                    $bl=false;
                                    //终止检查本个ip 继续检查下一个ip
                                    break;
                                }
                            }
                        }
                        //如果是true则找到有一个匹配成功的就返回
                        if($bl){
                            return;
                            die;
                        }
                    }
                }
                header('HTTP/1.1 403 Forbidden');
                die("当前IP地址不可访问!");
            }
        }

        //判断是否登录
        if(Session::has('Admin')){
            //当超过900秒不操作时删除当前session返回登录页面
            if (time() - Session::get('Admin.time') >= 900){
                //删除当前session
                Session::delete('Admin');
                //返回登录页面
                $this->redirect('Login/login');
            }else{
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

    /**
     * 图片文件上传
     */
    public function upload()
    {
        //接收上传的文件
        $file = request()->file('file');
        //移动到框架指定目录
        $info = $file->validate(['size'=>156780,'ext'=>'jpg,png,gif,ico,bmp'])->move('./uploads','');
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

}