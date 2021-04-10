<?php
/**
 * 用户控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use app\admin\validate\User as UserValidate;
use think\Db;
use think\facade\Request;
use app\admin\model\User as UserModel;
use think\facade\Session;

class User extends Base
{
    /**
     * 用户列表
     */
    public function list()
    {
        //查询所有用户
        $info = Db::name('user')->order('create_time','desc')->paginate(10);
        //给模板赋值
        $this->assign(['user'=>$info]);
        return $this->fetch('user/list');
    }

    /**
     * 用户添加
     */
    public function add()
    {
        if (request()->isAjax()){
            //接收前台传过来的数据
            $data = Request::param();
            //验证数据格式
            $validate = new UserValidate;
            if (!$validate->sceneAdd()->check($data)){
                $this->error($validate->getError());
            }
            //对密码进行加密
            $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
            //实例化对象
            $user = new UserModel();
            //执行添加并过滤非数据表字段
            $res = $user->allowField(true)->save($data);
            if ($res){
                //记录日志
                $this->log("添加了用户ID{$user->id}！");
                $this->success("添加成功！",'User/list');
            }else{
                //记录日志
                $this->log("添加用户ID{$user->id}失败！");
                $this->error("添加失败！");
            }
        }
        return $this->fetch('user/add');
    }

    /**
     * 用户查看
     */
    public function view()
    {
        //接收id
        $id = Request::param('id');
        //查询id的所有信息
        $info = Db::name('user')->where('id',$id)->find();
        //给模板赋值
        $this->assign(['user'=>$info]);
        return $this->fetch('user/view');
    }

    /**
     * 用户编辑
     */
    public function edit()
    {
        if (request()->isAjax())
        {
            //接收数据
            $data = Request::param();
            //验证数据格式
            $validate = new UserValidate;
            if (!$validate->sceneAdd()->check($data)){
                $this->error($validate->getError());
            }
            //实例化对象
            $user = new UserModel();
            //判断密码是否已经修改,则重新加密
            if ($data['password'] !== ""){
                $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
                //更新信息
                $res = $user->allowField(true)->save($data,['id'=>$data['id']]);
            }else{
                //删除传过来的空字符串密码
                unset($data['password']);
                //更新信息
                $res = $user->allowField(true)->save($data,['id'=>$data['id']]);
            }
            if ($res){
                //记录日志
                $this->log("更新了用户ID{$user->id}的个人信息！");
                $this->success("更新成功！",'User/list');
            }else{
                //记录日志
                $this->log("更新了用户ID{$user->id}的个人信息失败！");
                $this->error("更新失败！");
            }
        }
        //接收ID
        $id = Request::param('id');
        //查询id的所有信息
        $info = Db::name('user')->where('id',$id)->find();
        //给模板赋值
        $this->assign(['user'=>$info]);
        return $this->fetch('user/edit');
    }

    /**
     * 用户删除
     */
    public function delete()
    {
        if (request()->isAjax()){
            //接收前台传过来的ID
            $id = Request::param('id');
            //转为数组
            $array = explode(',',$id);
            //删除数组中的空元素
            foreach ($array as $key => $val){
                if($val == ""){
                    //执行删除
                    unset($array[$key]);
                }
            }
            //删除操作
            $res = Db::name('user')->delete($array);
            if ($res){
                //记录日志
                $this->log("删除了用户ID{$id}！");
                $this->success("删除成功!");
            }else{
                //记录日志
                $this->log("删除用户ID{$id}失败！");
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 金额充值
     */
    public function moneyAdd()
    {
        if (request()->isAjax()){
            //接收数据
            $data = Request::param();
            //更新金额
            $res = Db::name('user')->where('id',$data['id'])->setInc('money',$data['money']);
            if ($res){
                //记录日志
                $this->log("给用户ID{$data['id']}充值了{$data['money']}元！");
                $this->success("充值成功！");
            }else{
                //记录日志
                $this->log("给用户ID{$data['id']}充值{$data['money']}元失败！");
                $this->error("充值失败！");
            }
        }
    }

    /**
     * 搜索
     */
    public function search()
    {
        //接收搜索关键词
        $keywords = Request::param('keywords');
        //模糊查询
        $info = Db::name('user')->where('id|user|name|mobile|email|qq|introduction','like','%'.$keywords.'%')->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);
        //给模板赋值
        $this->assign(['user'=>$info]);
        return $this->fetch('user/list');
    }
}