<?php
/**
 * 后台首页控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;
use think\facade\Env;
use think\facade\Session;

class Index extends Base
{
    /**
     * 后台首页
     */
    public function index()
    {
        //查询管理员头像
        $info = Db::name('admin')->where('id',Session::get('Admin.id'))->field('photo')->find();
        //给模板赋值
        $this->assign(['admin'=>$info]);
        return $this->fetch('index');
    }

    /**
     * 我的桌面
     */
    public function desktop()
    {
        return $this->fetch('desktop');
    }

    /**
     * 清除缓存
     */
    public function clear()
    {
        if (request()->isAjax()){
            $CACHE_PATH = Env::get('runtime_path') . 'cache/';
            $TEMP_PATH = Env::get('runtime_path'). 'temp/';
            if (delete_dir_file($CACHE_PATH) && delete_dir_file($TEMP_PATH)) {
                $this->success("清除缓存成功!");
            } else {
                $this->error("清除缓存失败!");
            }
        }
    }

    /**
     * 退出登录
     */
    public function logOut()
    {
        if (request()->isAjax()){
            //删除当前session
            Session::delete('Admin');
            $this->success("退出成功！",'Login/login');
        }
    }

}