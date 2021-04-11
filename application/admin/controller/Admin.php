<?php
/**
 * 管理员控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;
use think\facade\Request;
use app\admin\validate\Admin as AdminValidate;
use app\admin\model\Admin as AdminModel;
use think\facade\Session;

class Admin extends Base
{
    /**
     * 管理员列表
     */
    public function list()
    {
        //获取当前管理员ID
        $id = Session::get('Admin.id');
        //如果当前管理员为超级管理员，则输出全部管理员信息
        if ($id == 1){
            //查询所有管理信息
            $info = Db::name('admin')->order('create_time','desc')->paginate(10);
        }else{
            //查询当前管理员的信息
            $info = Db::name('admin')->where('id',$id)->order('create_time','desc')->paginate(10);
        }
        //给模板赋值
        $this->assign(['admin'=>$info]);
        //渲染模板
        return $this->fetch('admin/list');
    }

    /*
     * 管理员添加
     */
    public function add()
    {
        //判断是否为ajax提交
        if (request()->isAjax()){
            //接收前台传过来的数据
            $data = Request::param();
            //验证数据格式
            $validate = new AdminValidate;
            if (!$validate->sceneAdd()->check($data)){
                $this->error($validate->getError());
            }
            //对密码进行加密
            $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
            //实例化对象
            $admin = new AdminModel();
            //执行添加并过滤非数据表字段
            $res = $admin->allowField(true)->save($data);
            //判断插入数据是否成功
            if ($res)
            {
                //中间表同时添加数据
                $access = Db::name('group_access')->insert(['uid'=>$admin->id,'group_id'=>$data['group']]);
                if ($access){
                    //记录日志
                    $this->log("添加了管理员：{$data['user']}");
                    $this->success("添加成功！",'Admin/list');
                }else{
                    //记录日志
                    $this->log("添加管理员{$data['user']}失败！");
                    $this->error("添加失败！");
                }
            }
        }
        //查询权限组数据表所有信息
        $info = Db::name('group')->where('id','>',1)->field('id,name')->select();
        //给模板赋值
        $this->assign(['add'=>$info]);
        return $this->fetch('admin/add');
    }

    /**
     * 管理员编辑
     */
    public function edit()
    {
        //接收前台传过来的ID值
        $id = Request::param('id');
        //查询当前管理员信息
        $info = Db::view('admin')
            ->view('group_access','group_id','admin.id=group_access.uid')
            ->where('id',$id)
            ->find();
        //判断是否为ajax请求
        if (request()->isAjax()){
            //接收前台传过来的数据
            $data = Request::param();
            //对数据进行验证
            $validate = new AdminValidate();
            if (!$validate->sceneEdit()->check($data)){
                $this->error($validate->getError());
            }
            //超级管理员和当前管理员（自己）的状态不能修改
            if ($data['id'] == 1 && $data['status'] == 0){
                $this->error("超级管理员状态不能修改！");
            }elseif($data['id'] == Session::get('Admin.id') && $data['status'] == 0){
                $this->error("自己的状态不能修改！");
            }else{
                //实例化对象
                $admin = new AdminModel();
                //如果为超级管理员，则可以修改密码，否则不行
                if (Session::get('Admin.id') == 1){
                    //判断密码是否已经修改,则重新加密
                    if ($data['password'] !== ""){
                        $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
                    }else{
                        //删除传过来的空字符串密码
                        unset($data['password']);
                    }
                    //执行更新并过滤非数据表字段
                    $res = $admin->allowField(true)->save($data,['id'=>$data['id']]);
                }else{
                    //执行更新并过滤非数据表字段
                    $res = $admin->allowField(true)->save($data,['id'=>$data['id']]);
                }
                if ($res){
                    //同时更新中间表
                    Db::name('group_access')->where('uid',$data['id'])->update(['group_id'=>$data['group']]);
                    //记录日志
                    $this->log("修改了管理员：{$info['user']}的个人信息！");
                    $this->success("更新成功！",'Admin/list');
                }else{
                    //记录日志
                    $this->log("修改管理员：{$data['user']}的个人信息失败！");
                    $this->error("更新失败！");
                }
            }
        }
        //查询权限组数据表所有信息
        $group = Db::name('group')->field('id,name')->select();
        //给模板赋值
        $this->assign(['admin'=>$info,'group'=>$group]);
        return $this->fetch('admin/edit');
    }

    /**
     * 管理员删除
     */
    public function delete()
    {
        if (request()->isAjax()){
            //接收前台传过来的ID
            $id = Request::param('id');
            //转为数组
            $array = explode(',',$id);
            //判断是否存在超级管理员，是则不能删除
            if (in_array(1,$array) == false){
                //判断是否存在自己,是则不能删除
                if (in_array(Session::get('Admin.id'),$array) == false){
                    //进行删除操作
                    $res = Db::name('admin')->delete($id);
                    if ($res){
                        //同时删除中间表关联
                        Db::name('group_access')->where('uid',$id)->delete();
                        $this->success("删除成功！");
                    }else{
                        $this->error("删除失败！");
                    }
                }else{
                    $this->error("自己不能删除！");
                }
            }else{
                $this->error("超级管理员不能可删除！");
            }
        }
    }

    /**
     * 管理员搜索列表
     */
    public function search()
    {
        //获取当前管理员ID
        $id = Session::get('Admin.id');
        //接收搜索关键词
        $keywords = Request::param('keywords');
        //如果当前管理员为超级管理员，则输出全部管理员信息
        if ($id == 1){
            //查询所有管理信息
            $info = Db::name('admin')->where('id|user|name|mobile|email','like','%'.$keywords.'%')->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);
        }else{
            //查询当前管理员的信息
            $info = Db::name('admin')->where('id',$id)->where('id|user|name|mobile|email','like','%'.$keywords.'%')->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);
        }
        //给模板赋值
        $this->assign(['admin'=>$info]);
        //渲染模板
        return $this->fetch('admin/list');
    }

}