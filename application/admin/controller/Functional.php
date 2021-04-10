<?php
/**
 * 功能配置控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;
use think\facade\Request;
use app\admin\model\Email;

class Functional extends Base
{
    /**
     * 支付配置
     */
    public function pay()
    {
        //查询所有支付配置信息
        $info = Db::name('pay')->where('id',1)->find();
        //给模板赋值
        $this->assign(['fun'=>$info]);
        //模板渲染
        return $this->fetch('functional/pay');
    }

    /**
     * 支付配置编辑
     */
    public function payEdit()
    {
        //判断当前是否为ajax请求
        if (request()->isAjax()){
            $data = Request::param();
            //当存在数据时执行更新数据
            $res = Db::name('pay')->where('id',1)->update($data);
            //判断返回的值是否为true
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
    }

    /**
     * 短信配置
     */
    public function sms()
    {
        //查询短信配置信息
        $info = Db::name('sms')->where('id',1)->find();
        //给模板赋值
        $this->assign(['sms'=>$info]);
        //模板渲染
        return $this->fetch('functional/sms');
    }

    /**
     * 短信配置编辑
     */
    public function smsEdit()
    {
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台的传过来的值
            $data = Request::param();
            //对密码进行MD5算法加密
            $data['smsbao_pass'] = md5($data['smsbao_pass']);
            //当存在数据时执行更新数据
            $res = Db::name('sms')->strict(false)->where('id',1)->update($data);
            //判断返回的值是否为true
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
    }

    /**
     * 短信测试
     */
    public function testSms()
    {
        if (request()->isAjax()){
            //接收数据
            $data = Request::param();
            //对密码进行MD5算法加密
            $data['smsbao_pass'] = md5($data['smsbao_pass']);
            //调用随机验证码方法
            $code = $this->codeStr(2);
            //验证码有效时间(单位：分钟)
            $time = 5;
            //自定义测试短信内容
            $content = "【测试】这是一条测试内容，您的验证码是{$code}，在{$time}分钟有效。";
            //调用发送函数
            $res = sendSms($data['smsbao_account'],$data['smsbao_pass'],$content,$data['smsbao_phone']);
            if ($res == 0){
                $this->success("发送成功！");
            }else{
                $this->error("发送失败！");
            }
        }
    }

    /**
     * 邮件配置
     */
    public function email()
    {
        //查询邮件配置信息
        $info = Db::name('email')->where('id',1)->find();
        //给模板赋值
        $this->assign(['email'=>$info]);
        return $this->fetch('functional/email');
    }

    /**
     * 邮件配置编辑
     */
    public function emailEdit()
    {
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的信息
            $data = Request::param();
            //实例化对象
            $email = new Email();
            //更新操作并过滤非数据表字段
            $res = $email->allowField(true)->save($data,['id'=>1]);
            //判断是否执行成功，true为成功
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
    }

    /**
     * 测试邮件发送
     */
    public function testEmail()
    {
        //判断是否为ajax请求
        if (request()->isajax()){
            //接收前台信息
            $data = Request::param();
            //数据模拟定义
            $name = "我叫测试";
            $title = "这是邮件发送测试标题";
            $content = "我是邮件发送测试的内容！";
            //执行测试发送
            $res = sendEmail($data['email'],$data['key'],$data['stmp'],$data['sll'],$name,$title,$content,$data['test_email']);
            if ($res){
                $this->success("发送成功！");
            }else{
                $this->error("发送失败！");
            }
        }
    }

    /*
     * 第三方登录配置
     */
    public function thirdparty()
    {
        //查询第三方登录配置信息
        $info = Db::name('thirdparty')->where('id',1)->find();
        //给模板赋值
        $this->assign(['thirdparty'=>$info]);
        //模板渲染
        return $this->fetch('functional/thirdparty');
    }

    /**
     * 第三方登录配置编辑
     */
    public function thirdpartyEdit()
    {
        //判断当前请求是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的值
            $data = Request::param();
            //执行更新操作
            $res = Db::name('thirdparty')->where('id',1)->update($data);
            //判断是否操作成功，true为操作成功
            if ($res){
                $this->success("更新成功！");
            }else{
                $this->error("更新失败！");
            }
        }
    }
}