<?php
/**
 * 权限组验证器
 * by:小航 QQ:11467102
 */
namespace app\admin\validate;
use think\Validate;

class Group extends Validate
{
    //规则
    protected $rule = [
        'name'          => 'require',
        'rules'         =>  'require',
    ];

    //自定义提示信息
    protected $message = [
        'name.require'      =>  '权限组名称不能为空！',
        'rules.require'     =>  '选择权限不能为空！',
    ];

    //add场景验证
    public function sceneAdd()
    {
        return $this->only(['name,rules']);
    }

    //edit场景验证
    public function sceneEdit()
    {
        return $this->only(['name,rules']);
    }
}