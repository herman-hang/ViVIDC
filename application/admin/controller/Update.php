<?php
/**
 * 系统升级控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;

class Update extends Base
{
    protected $url = "http://localhost/versions/update.log";
    /**
     * 版本检测
     */
    public function checkVersion()
    {
        //远程读取版本信息
        $log = get_file($this->url);
        if ($log == false){
            return ['code'=>0,'message'=>"获取版本信息失败！"];
        }
        //将版本信息转为数组
        $array = explode(',',$log);
        //截取出版本号
        $version = array_shift($array);
        //判断是否获取到了版本号
        if ($version == NULL){
            return ['code'=>0,'message'=>"获取版本号失败，请重试！"];
        }
        //查询当前版本号
        $system = Db::name('system')->where('id',1)->field('version')->find();
        //判断是否要更新
        if (version_compare($system['version'],$version,'<')){
            //返回数据
            return ['code'=>1,'version'=>$version,'log'=>$array];
        }else{
            //返回数据
            return ['code'=>1,'message'=>"当前已经是最新版本！"];
        }
    }
}