<?php
/**
 * 管理员登录控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Admin;
use app\admin\model\System;
use think\facade\Request;
use think\Db;
use think\facade\Session;
use app\admin\validate\Login as LoginValidate;

class Login extends Base
{
    /**
     * 初始化
     */
    protected function initialize()
    {
    }

    /**
     * 登录渲染
     */
    public function login()
    {
        //防止重复登录
        if (Session::has('Admin')){
            $this->redirect('Index/index');
        }

        if(request()->isAjax()){
            //接收前台传过来的user值
            $info = Request::param();
            //实例化对象
            $validate = new LoginValidate;
            //验证数据
            if (!$validate->check($info)) {
                $this->error($validate->getError());
            }
            //查询当前管理员信息
            $admin = Admin::where('user|email|mobile',$info['user'])->field('id,password,status,login_sum,error_time,login_error,ban_time')->find();
            //查询系统设置信息
            $system = System::where('id',1)->field('max_logerror')->find();
            //记录登录错误时间,1800秒后所有的记录清零
            $error_time = time() + 1800;
            //封禁时间常量，单位：分钟
            $BAN = 30;
            //登录错误次数到达后的封禁时间
            $ban_time = time() + $BAN*60;
            if ($admin == NULL){
                $this->error("管理员不存在！");
            }else{
                //存当前时间戳
                $Admin['time'] = time();
                //存当前管理员ID
                $Admin['id'] = $admin['id'];
                //判断密码错误的Session是否存在
                if (!Session::has('passError')){
                    if ($admin['status'] == 0){
                        $this->error("管理员已停用！");
                    }else{
                        if ($admin['status'] == 1){
                            //判断密码是否相等
                            if (password_verify($info['password'],$admin['password']) == true){
                                // 设置Session
                                Session::set('Admin',$Admin);
                                //登录总次数自增1
                                $admin->setInc('login_sum');
                                $this->success("欢迎回来！",'Index/index');
                            }else{
                                //记录密码登录错误时间,便于$error_time分钟后对登录错误次数清零
                                $admin -> save(['error_time'=>$error_time]);
                                //登录密码错误次数自增1
                                $admin->setInc('login_error');
                                //设置密码登录错误session值,便于跳出验证码
                                Session::set('passError',time());
                                $this -> error("密码错误!");
                            }
                        }else{
                            $this->error("非法请求！");
                        }
                    }
                }else{
                    if(!captcha_check($info['code'])){
                        // 验证失败
                        $this->error("验证码错误！");
                    }
                    //解除登录错误时间，恢复初始化
                    if ($admin->getData('error_time') <= time()){
                        //错误时间清空
                        $admin->save(['error_time' => NULL]);
                        //将登录错误次数清零
                        $admin->save(['login_error'=> 0]);
                        //这里判断是防止封禁时间大于等于登录错误时间出现的BUG
                        if ($admin->getData('ban_time') == NULL || $admin->getData('ban_time') <= time()){
                            //将封禁时间设置为空
                            $admin->save(['ban_time' => NULL]);
                            if ($admin['status'] == 0){
                                $this->error("管理员已停用！");
                            }else{
                                if ($admin['status'] == 1){
                                    if (password_verify($info['password'],$admin['password']) == true){
                                        // 设置Cookie 有效期为 900秒
                                        Session::set('Admin',$Admin);
                                        //登录总次数自增1
                                        $admin->setInc('login_sum');
                                        //删除密码错误记录的session值
                                        Session::delete('passError');
                                        $this->success("欢迎回来！",'Index/index');
                                    }else{
                                        //记录密码登录错误时间,便于$error_time分钟后对登录错误次数清零
                                        $admin -> save(['error_time'=>$error_time]);
                                        //登录密码错误次数自增1
                                        $admin->setInc('login_error');
                                        //获取当前登录错误次数
                                        $error_count = $admin->getData('login_error');
                                        //获取允许登录错误最大次数
                                        $max_error = $system->getData('max_logerror');
                                        $count = $max_error - $error_count;
                                        $this->error("密码错误，还有{$count}次机会！");
                                    }
                                }else{
                                    $this->error("非法请求！");
                                }
                            }
                        }else{
                            //计算剩余多少分钟解封，这里强制转为int类型
                            $time = (int)($admin->getData('ban_time') - time()/60);
                            $this->error("登录错误过多，请{$time}分钟后再试！");
                        }
                    }else{
                        //判断当前的封禁时间是否为空
                        if ($admin->getData('ban_time') == NULL || $admin->getData('ban_time') <= time()){
                            //将封禁时间设置为空
                            $admin->save(['ban_time' => NULL]);
                            if ($admin['status'] == 0){
                                $this->error("管理员已停用！");
                            }else{
                                if ($admin['status'] == 1){
                                    //判断登录错误次数是否大于或等于指定登录错误次数
                                    if ($admin->getData('login_error') >= $system->getData('max_logerror')){
                                        //封禁时间写入
                                        $admin->save(['ban_time'=>$ban_time]);
                                        $this->error("登录错误过多，请{$BAN}分钟后再试！");
                                    }else{
                                        if (password_verify($info['password'],$admin['password']) == true){
                                            // 设置Cookie 有效期为 900秒
                                            Session::set('Admin',$Admin);
                                            //登录总次数自增1
                                            $admin->setInc('login_sum');
                                            //登录错误次数清零
                                            $admin->save(['login_error'=>0]);
                                            //删除密码错误记录的session值
                                            Session::delete('passError');
                                            //登录错误时间清空
                                            $admin->save(['error_time'=>NULL]);
                                            $this->success("欢迎回来！",'Index/index');
                                        }else{
                                            //记录密码登录错误时间,便于$error_time分钟后对登录错误次数清零
                                            $admin -> save(['error_time'=>$error_time]);
                                            //登录密码错误次数自增1
                                            $admin->setInc('login_error');
                                            //获取当前登录错误次数
                                            $error_count = $admin->getData('login_error');
                                            //获取允许登录错误最大次数
                                            $max_error = $system->getData('max_logerror');
                                            $count = $max_error - $error_count;
                                            $this->error("密码错误，还有{$count}次机会！");
                                        }
                                    }
                                }else{
                                    $this->error("非法请求！");
                                }
                            }
                        }else{
                            //计算剩余多少分钟解封，这里强制转为int类型
                            $time = (int)($admin->getData('ban_time') - time()/60);
                            $this->error("登录错误过多，请{$time}分钟后再试！");
                        }
                    }
                }
            }
        }
        return $this->fetch('login');
    }

}