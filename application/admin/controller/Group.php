<?php
/**
 * 权限组控制器
 * by:小航 QQ:11467102
 */

namespace app\admin\controller;
use think\Db;
use app\admin\model\Admin;
use app\admin\model\Group as GroupModel;
use think\facade\Request;
use app\admin\validate\Group as GroupValidate;

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
            //关联查询
            $user = Db::view('group_access','uid,group_id')
                ->view('Admin','id,user','group_access.uid=admin.id')
                ->where('group_id',$val['id'])
                ->select();
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
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的信息
            $data = Request::param();
            //验证数据
            $validate = new GroupValidate();
            if (!$validate->sceneAdd()->check($data)){
                //验证不通过输出提示信息
                $this->error($validate->getError());
            }
            //实例化对象
            $group = new GroupModel();
            //执行添加并过滤数据表不存在的字段
            $res = $group->allowField(true)->save($data);
            if ($res){
                $this->success("添加成功！",'Group/list');
            }else{
                $this->error("添加失败！");
            }
        }
        //查询一级ID
        $one = Db::name('rule')->where('pid',0)->field(['id','name'])->select();
        //循环一级数组
        foreach($one as $key => $val){
            //查询二级ID
            $two = Db::name('rule')->where('pid',$val['id'])->field(['id','name','pid'])->select();
            $one[$key]['two'] = $two;
            //循环二级数组
            foreach ($one[$key]['two'] as $item => $value){
                $three = Db::name('rule')->where('pid',$value['id'])->field(['id','name,pid as ppid'])->select();
                //循环将pid赋值给$three
                for ($i=0; $i<count($three); $i++){
                    $three[$i]['pid'] = $value['pid'];
                }
                $one[$key]['two'][$item]['three'] = $three;
            }
        }
        $this -> assign(['add'=>$one]);
        return $this->fetch('add');
    }

    /**
     * 权限组编辑
     */
    public function edit()
    {
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的信息
            $data = Request::param();
            //验证数据
            $validate = new GroupValidate();
            if (!$validate->sceneEdit()->check($data)){
                //验证不通过输出提示信息
                $this->error($validate->getError());
            }
            //实例化对象
            $group = new GroupModel();
            //执行添加并过滤数据表不存在的字段
            $res = $group->allowField(true)->save($data,['id'=>$data['id']]);
            if ($res){
                $this->success("更新成功！",'Group/list');
            }else{
                $this->error("更新失败！");
            }
        }
        //接收编辑数据ID
        $id = Request::param('id');
        //查询数据
        $info = Db::name('group')->where('id',$id)->find();
        //切割字符串转为数组
        $info['rules'] = explode(',',$info['rules']);
        //遍历数组删除为空的元素
        foreach($info['rules'] as $k=>$v){
            if($v == ""){
                //执行删除
                unset($info['rules'][$k]);
            }
        }
        //查询一级ID
        $one = Db::name('rule')->where('pid',0)->field(['id','name'])->select();
        //循环一级数组
        foreach($one as $key => $val){
            //查询二级ID
            $two = Db::name('rule')->where('pid',$val['id'])->field(['id','name','pid'])->select();
            $one[$key]['two'] = $two;
            //循环二级数组
            foreach ($one[$key]['two'] as $item => $value){
                $three = Db::name('rule')->where('pid',$value['id'])->field(['id','name,pid as ppid'])->select();
                //循环将pid赋值给$three
                for ($i=0; $i<count($three); $i++){
                    $three[$i]['pid'] = $value['pid'];
                }
                $one[$key]['two'][$item]['three'] = $three;
            }
        }
        //赋值变量给模板
        $this -> assign(['edit'=>$one,'rules'=>$info]);
        return $this->fetch('edit');
    }

    /**
     * 权限组删除操作
     */
    public function delete()
    {
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的ID
            $id = Request::param('id');
            //进行删除操作
            $res = Db::name('group')->delete($id);
            if ($res){
                $this->success("删除成功！");
            }else{
                $this->error("删除失败！");
            }
        }
    }

}