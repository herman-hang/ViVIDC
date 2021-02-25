<?php
/**
 * 功能配置控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Db;
use think\facade\Request;

class Functional extends Base
{
    /**
     * 支付配置
     */
    public function pay()
    {
        //查询所有支付配置信息
        $info = Db::name('pay')->where('id',1)->find();
        //判断当前是否为ajax请求
        if (request()->isAjax()){
            $data = Request::param();
            //判断当前查询的数据是否为空
            if ($info == NULL){
                //当不存在数据时直接插入
                $res = Db::name('pay')->insert($data);
            }else{
                //当存在数据时执行更新数据
                $res = Db::name('pay')->where('id',1)->update($data);
            }
            //判断返回的值是否为true
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        //给模板赋值
        $this->assign(['fun'=>$info]);
        //模板渲染
        return $this->fetch('pay');
    }

    /**
     * 短信配置
     */
    public function sms()
    {
        //查询短信配置信息
        $info = Db::name('sms')->where('id',1)->find();
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台的传过来的值
            $data = Request::param();
            //判断当前查询的数据是否为空
            if ($info == NULL){
                //当不存在数据时直接插入
                $res = Db::name('sms')->insert($data);
            }else{
                //当存在数据时执行更新数据
                $res = Db::name('sms')->where('id',1)->update($data);
            }
            //判断返回的值是否为true
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        //给模板赋值
        $this->assign(['sms'=>$info]);
        //模板渲染
        return $this->fetch('sms');
    }

    /**
     * 邮件配置
     */
    public function email()
    {
        //查询邮件配置信息
        $info = Db::name('email')->where('id',1)->find();
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的信息
            $data = Request::param();
            //判断当前查询数据是否为空
            if ($info == NULL){
                //执行插入数据操作
                $res = Db::name('email')->insert($data);
            }else{
                $res = Db::name('email')->where('id',1)->update($data);
            }
            //判断是否执行成功，true为成功
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        //给模板赋值
        $this->assign(['email'=>$info]);
        return $this->fetch('email');
    }

    /*
     * 第三方登录配置
     */
    public function thirdparty()
    {
        //查询第三方登录配置信息
        $info = Db::name('thirdparty')->where('id',1)->find();
        //判断当前请求是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的值
            $data = Request::param();
            //判断当前查询的数值是否为空
            if ($info == NULL){
                //为空执行插入数据操作
                $res = Db::name('thirdparty')->insert($data);
            }else{
                //执行更新操作
                $res = Db::name('thirdparty')->where('id',1)->update($data);
            }
            //判断是否操作成功，true为操作成功
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
        //给模板赋值
        $this->assign(['thirdparty'=>$info]);
        //模板渲染
        return $this->fetch('thirdparty');
    }
}