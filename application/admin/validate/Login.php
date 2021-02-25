<?php
/**
 * 登录验证器
 * by:小航 QQ:11467102
 */
namespace app\admin\validate;
use think\Validate;

class Login extends Validate
{
    //规则
    protected $rule =   [
        'user'       => 'require|min:5|max:16',
        'password'   => 'require|min:6|max:16',
    ];
    //自定义提示信息
    protected $message  =   [
        'user.require'      => '账号不能为空！',
        'user.min'          => '账号位数过短！',
        'user.max'          => '账号位数过长！',
        'password.require'  => '密码不能为空！',
        'password.min'      => '密码位数过短！',
        'password.max'      => '密码位数过长！',
    ];
}