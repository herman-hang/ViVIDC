<?php
/**
 * 系统管理控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\controller\Base;
use think\facade\Request;
use think\Db;
use app\admin\model\System as SystemModel;
use app\admin\validate\System as SystemValidate;

class System extends Base
{
    /**
     * 系统信息
     */
    public function system()
    {
        //当前系统信息
        $system = Db::name("system")->where('id',1)->find();
        if (request()->isAjax()){
            //接收所有提交数值
            $data = Request::param();
            //验证
            $validate = new SystemValidate;
            if (!$validate->sceneSystem()->check($data)){
                $this->error($validate->getError());
            }
            //执行更新操作
            $res = Db::name('system')->where('id',1)->update($data);
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$system]);
        return $this->fetch('system');
    }

    /**
     * 安全配置
     */
    public function security()
    {
        //当前的信息
        $info = Db::name('system')->where('id',1)->field('max_logerror,ip')->find();
        if (request()->isAjax()){
            //接收数值
            $data = Request::param();
            $validate = new SystemValidate;
            //验证数值
            if (!$validate->sceneSecurity()->check($data)){
                $this->error($validate->getError());
            }
           if ($data['ip'] == NULL) {
               //更新数据
               $res = Db::name('system')->where('id',1)->update(['max_logerror'=>$data['max_logerror']]);
           }else{
               //更新数据
               $res = Db::name('system')->where('id',1)->update($data);
           }
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$info]);
        return $this->fetch('security');
    }

    /**
     * 屏蔽词
     */
    public function block()
    {
        $info = Db::name('system')->where('id',1)->field('block')->find();
        if (request()->isAjax()){
            //接收前台传过来的数值
            $data = Request::param();
            $res = Db::name('system')->where('id',1)->update($data);
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        $this->assign(['system'=>$info]);
        return $this->fetch('block');
    }
}