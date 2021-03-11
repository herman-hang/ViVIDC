<?php
/**
 * 日志记录控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\model\AdminLog as ModelAdminLog;
use think\Db;
use think\facade\Session;

class AdminLog extends Base
{
    /**
     * 登录日志
     */
    public function loginLog()
    {
        //关联查询
        $info = Db::view('admin_log')
            ->view('admin','user','admin.id=admin_log.admin_id')
            ->where('admin_log.admin_id',session::get('Admin.id'))
            ->where('type',1)
            ->order('create_time','desc')
            ->paginate(10);
        //给模板赋值
        $this->assign(['log'=>$info]);
        return $this->fetch('list');
    }

    /**
     * 操作日志
     */
    public function operationLog()
    {
        //关联查询
        $info = Db::view('admin_log')
            ->view('admin','user','admin.id=admin_log.admin_id')
            ->where('admin_log.admin_id',session::get('Admin.id'))
            ->where('type',2)
            ->order('create_time','desc')
            ->paginate(10);
        //给模板赋值
        $this->assign(['log'=>$info]);
        return $this->fetch('list');
    }

}