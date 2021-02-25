<?php
/**
 * 系统管理验证器
 * by:小航 QQ:11467102
 */
namespace app\admin\validate;
use think\Validate;

class System extends Validate
{
    //规则
    protected $rule =   [
        'email'         =>  'email',
        'qq'            =>  'min:5|max:11|number',
        'usergroup'     =>  'min:6|max:9|number',
        'max_logerror'  =>  'number'
    ];

    //自定义提示信息
    protected $message = [
        'email'                 =>  '邮箱格式错误！',
        'qq.min'                =>  'QQ位数过短！',
        'qq.max'                =>  'QQ位数过长！',
        'qq.number'             =>  'QQ号必须纯数字！',
        'usergroup.min'         =>  'QQ群位数过短！',
        'usergroup.max'         =>  'QQ群位数过长！',
        'usergroup.number'      =>  'QQ群必须纯数字！',
        'max_logerror.number'   =>  '允许登录错误次数必须是纯数字！',
    ];

    //system场景验证
    public function sceneSystem()
    {
        return $this->only(['email,qq,usergrop']);
    }

    //security场景验证
    public function sceneSecurity()
    {
        return $this->only(['max_logerror']);
    }
}