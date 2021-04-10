<?php
/**
 * 第三方快捷登录控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;

class Oauths extends Base
{
    //登录地址
    public function login($type = null)
    {
        if ($type == null) {
            $this->error('参数错误');
        }
        //查询所有配置信息
        $info = Db::name('thirdparty')->where('id',1)->find();
        switch ($type){
            case "qq";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/qq";
                $OAuth = new \Yurun\OAuthLogin\QQ\OAuth2($info['qq_appid'], $info['qq_secret'], $callback);
                break;
            case "weixin";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/weixin";
                $OAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($info['wx_appid'], $info['wx_secret'], $callback);
                break;
            case "sina";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/sina";
                $OAuth = new \Yurun\OAuthLogin\Weibo\OAuth2($info['weibo_appid'], $info['weibo_secret'], $callback);
                break;
            default:
                $this->error("非法请求！");
        }
        //调用getAuthUrl方法获取state
        $url = $OAuth->getAuthUrl();
        //设置session,方便回调验证,防止跨站请求伪造（CSRF）攻击
        Session::set('state',$OAuth->state);
        header('location:' .$url);
    }

    //授权回调地址
    public function callback($type = null)
    {
        //查询所有配置信息
        $info = Db::name('thirdparty')->where('id',1)->find();
        switch ($type){
            case "qq";
                $loginType = "QQ";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/qq";
                $OAuth = new \Yurun\OAuthLogin\QQ\OAuth2($info['qq_appid'], $info['qq_secret'], $callback);
                // 获取accessToken
                $accessToken = $OAuth->getAccessToken(Session::get('state'));
                // 用户唯一标识
                $openid = $OAuth->openid;
                $admin = Db::name('admin')->where('qq_openid',$openid)->field('id')->find();
                break;
            case "wein";
                $loginType = "微信";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/weixin";
                $OAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($info['wx_appid'], $info['wx_secret'], $callback);
                // 获取accessToken
                $accessToken = $OAuth->getAccessToken(Session::get('state'));
                // 用户唯一标识
                $openid = $OAuth->openid;
                $admin = Db::name('admin')->where('weixin_openid',$openid)->field('id')->find();
                break;
            case "sina";
                $loginType = "微博";
                //定义回调地址
                $callback = Request::domain() ."/". "viv.php/oauths/callback/type/sina";
                $OAuth = new \Yurun\OAuthLogin\Weibo\OAuth2($info['weibo_appid'], $info['weibo_secret'], $callback);
                // 获取accessToken
                $accessToken = $OAuth->getAccessToken(Session::get('state'));
                // 用户唯一标识
                $openid = $OAuth->openid;
                $admin = Db::name('admin')->where('weibo_openid',$openid)->field('id')->find();
                break;
            default:
                $this->error("非法请求！");
        }
        if ($accessToken !== ""){
            //判断是否已经判断管理员
            if ($admin){
                //存当前时间戳
                $Admin['time'] = time();
                //存当前管理员ID
                $Admin['id'] = $admin['id'];
                // 设置Session
                Session::set('Admin',$Admin);
                //登录总次数自增1
                Db::name('admin')->where('id',$admin['id'])->setInc('login_sum');
                //记录日志
                $this->log("使用{$loginType}快捷登录成功！",1);
                $this->redirect('Index/index');
            }else{
                //设置openid的session,方便登录成功后进行绑定
                $oauth['type'] =  $type;
                $oauth['openid'] = $openid;
                Session::set('Oauth',$oauth);
                //跳转到登录页面
                $this->redirect('Login/login');
            }
        }else{
            $this->error("获取第三方用户的基本信息失败！");
        }
    }
}