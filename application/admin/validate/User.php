<?php
/**
 * 用户验证器
 * by:小航 QQ:11467102
 */

namespace app\admin\validate;
use think\Validate;

class User extends Validate
{
    //规则
    protected $rule = [
        'user'          => 'min:5|max:16|alphaNum|unique:user',
        'name'          => 'chs',
        'number'        => 'idCard|unique:user',
        'password'      => 'min:6',
        'passwords'     => 'confirm:password',
        'mobile'        => 'mobile|unique:user',
        'email'         => 'email|unique:user',
        'qq'            => 'number|min:5|max:11',
        'age'           => 'number|between:1,120',
    ];

    //自定义提示信息
    protected $message = [
        'user.require'      => '用户名不能为空！',
        'user.min'          => '用户名位数过短！',
        'user.max'          => '用户名位数过长！',
        'user.alphaNum'     => '用户名只能是字母和数字组成！',
        'user.unique'       => '用户名已存在！',
        'name.require'      => '真实姓名不能为空！',
        'name.chs'          => '真实姓名只能是汉字！',
        'number.idCard'     => '身份证格式不正确！',
        'number.unique'     => '该身份证号码已经实名过！',
        'password.require'  => '密码不能为空！',
        'password.min'      => '密码位数过短！',
        'passwords.require' => '请输入确认密码！',
        'passwords.confirm' => '两次密码不一致！',
        'mobile.mobile'     => '手机号码格式不正确！',
        'mobile.require'    => '手机号码不能为空！',
        'mobile.unique'     => '手机号码已存在！',
        'email.email'       => '邮箱格式不正确！',
        'email.unique'      => '邮箱已存在！',
        'qq.number'         => 'QQ必须是数字！',
        'qq.min'            => 'QQ号码不能小于5位！',
        'qq.max'            => 'QQ号码过长！',
        'age.number'        => '年龄必须是数字！',
        'age.between'       => '年龄只能在1-120岁之间！',
        'age.require'       => '年龄不能为空！',
    ];
    //add场景验证
    public function sceneAdd()
    {
        return $this->only(['user,name,number,password,passwords,mobile,email,qq,age']);
    }

    //edit场景验证
    public function sceneEdit()
    {
        return $this->only(['user,name,number,password,passwords,mobile,email,qq,age']);
    }

}