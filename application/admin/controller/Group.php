<?php
/**
 * 权限组控制器
 * by:小航 QQ:11467102
 */

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Db;
use app\admin\model\Admin;
use app\admin\model\Group as GroupModel;

class Group extends Base
{
    /**
     * 权限组列表
     */
    public function list()
    {
        //查询所有权限组信息
        $info = GroupModel::order('create_time','desc')->paginate(10);
        foreach ($info as $key=>$val){
            $user = Db::name('admin')->where('group',$val['id'])->field('user')->select();
            $info[$key]['user'] = $user;
        }
        //给模板赋值
        $this->assign(['group'=>$info]);
        return $this->fetch('list');
    }

    /**
     * 权限组添加
     */
    public function add()
    {
        $id = Db::name('rule')->where('pid',0)->field('id')->select();
        foreach($id as $key => $val){
            dump($val);
        }
        return $this->fetch('add');
    }

}