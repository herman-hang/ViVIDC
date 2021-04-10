<?php
/**
 * 系统升级控制器
 * by:小航 QQ:11467102
 */
namespace app\admin\controller;
use think\Db;
use think\facade\Config;//获取配置类
use think\facade\Cookie;

class Update extends Base
{
    //日志内容URL
    protected $url;
    //版本集URL
    protected $versions;
    //升级包
    protected $zipUrl;
    //文件备份目录
    protected $backup;
    //升级包存放目录
    protected $download;

    /**
     * 初始化
     */
    protected function initialize()
    {
        //定义日志内容URL
        $this->url = "http://localhost/versions/update.log";
        //定义版本集URL
        $this->versions = "http://localhost/versions/versions.txt";
        //定义升级包下载地址
        $this->zipUrl = "http://localhost/versions";
        //定义文件备份目录
        $this->backup = $_SERVER['DOCUMENT_ROOT'] . "/update/backup";
        //定义升级包存放目录
        $this->download = $_SERVER['DOCUMENT_ROOT'] . "/update/download";
        //继承父类初始化方法
        return parent::initialize();
    }

    /**
     * 版本检测
     */
    public function checkVersion()
    {
        //远程读取版本信息
        $log = $this->getFile($this->url);
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

    /**
     * 系统升级操作
     */
    public function update()
    {
        //查询当前系统版本
        $system = Db::name('system')->where('id',1)->field('version')->find();
        //记录日志
        $this->writeLog("开始读取系统升级日志！");
        //获取升级日志内容信息
        $log = $this->getFile($this->url);
        if ($log == false){
            //记录日志
            $this->writeLog("读取系统升级日志失败，请重试！");
            return ['code'=>0,'message'=>"读取系统升级日志失败，请重试！"];
        }
        //将升级日志内容信息转为数组
        $array = explode(',',$log);
        //记录日志
        $this->writeLog("获取需要升级的版本号！");
        //截取出版本号
        $version = array_shift($array);
        //判断是否获取到了版本号
        if ($version == NULL){
            //记录日志
            $this->writeLog("获取版本号失败，请重试！");
            return ['code'=>0,'message'=>"获取版本号失败，请重试！"];
        }
        //判断当前版本是否低于升级版本(返回值-1为第一个版本低于第二个版本，0为两者相等，1为高于第二个版本)
        if (version_compare($system['version'],$version) == -1){
            //记录日志
            $this->writeLog("获取版本集信息！");
            //获取版本集内容
            $versions = $this->getFile($this->versions);
            if ($versions == false){
                //记录日志
                $this->writeLog("获取版本集信息失败，请重试！");
                return ['code'=>0,'message'=>"获取版本集信息失败，请重试！"];
            }
            //将版本集转为数组
            $versions = explode(';',$versions);
            //查询当前版本号在数组中的下标
            $key = array_search($system['version'],$versions);
            //判断是直接升级还是迭代升级
            if (version_compare($version,$versions[$key+1]) == 0){
                //记录日志
                $this->writeLog("直接升级开始！");
                //直接升级，调用升级函数
                $this->execute($versions[$key+1]);
                //记录日志
                $this->writeLog("升级完成！");
                return ['code'=>1,'message'=>"升级完成！"];
            }else{
                //记录日志
                $this->writeLog("迭代升级开始！");
                //迭代升级
                for ($key = $key + 1 ; $key <= count($versions); $key++){
                    //记录日志
                    $this->writeLog("开始升级{$versions[$key]}版本！");
                    //调用更新函数
                    $this->execute($versions[$key]);
                    //当前版本等于升级版本的时候跳出循环
                    if (version_compare($versions[$key],$version) == 0){
                        //记录日志
                        $this->writeLog("**********************升级完成!**********************");
                        return ['code'=>1,'message'=>"升级完成！"];
                    }
                }
            }
        }
    }

    /**
     * 执行升级
     * $version 需要升级的版本号
     */
    public function execute($version)
    {
        //引入数据库操作类
        include_once '../extend/baksql/Baksql.php';
        // PclZip类库不支持命名空间
        include_once '../extend/pclzip/PclZip.php';
        //查询当前系统版本
        $system = Db::name('system')->where('id',1)->field('version')->find();
        //指定升级包URL
        $zipUrl = $this->zipUrl."/".$version.".zip";
        //指定升级包下载指定目录
        $downPath = $this->download."/".$version;
        //判断该目录是否存在
        if (!file_exists($downPath)) {
            //创建文件
            mkdir($downPath,0777,true);
        }
        //记录日志
        $this->writeLog("下载升级包！");
        //下载升级包操作
        $downData = $this->downFile($zipUrl,$downPath);
        if (!$downData){
            //记录日志
            $this->writeLog("下载升级包失败！");
            return ['code'=>0,'message'=>"下载升级包失败！"];
        }
        //记录日志
        $this->writeLog("解压升级包！");
        //解压升级包
        $dealData = $this->dealZip($downData['save_path'],$downPath);
        if (!$dealData){
            //记录日志
            $this->writeLog("解压升级包失败！");
            return ['code'=>0,'message'=>"解压升级包失败！"];
        }
        //判断配置文件是否存在
        if (!file_exists($downPath."/"."config.php")){
            //记录日志
            $this->writeLog("配置文件不存在！");
            return ['code'=>0,'message'=>"配置文件不存在！"];
        }
        //引入升级包的配置文件
        $config = include($downPath."/"."config.php");
        //判断是否需要更新代码，需要则备份代码并更新
        if ($config['is_code']){
            //记录日志
            $this->writeLog("开始网站备份！");
            //定义备份目录路径
            $backupPath = $this->backup."/".$system['version'];
            //判断该目录是否存在
            if (!file_exists($backupPath)) {
                //创建文件
                mkdir($backupPath,0777,true);
            }
            //进行备份网站代码
            $backFile = $backupPath."/".$system['version'].".zip";
            $codeBackupStatus = $this->backupCode($backFile);
            if (!$codeBackupStatus){
                //记录日志
                $this->writeLog("网站备份失败！");
                return ['code'=>0,'message'=>"网站备份失败！"];
            }
            //记录日志
            $this->writeLog("备份成功，开始升级操作！");
            //升级操作,将文件覆盖到网站根目录的上一层目录(即public目录的上一层)
            $copyStatus = $this->copyFile($downPath."/"."temp",dirname($_SERVER['DOCUMENT_ROOT']));
            //覆盖失败
            if (!$copyStatus){
                //记录日志
                $this->writeLog("升级失败，代码进行回滚！");
                //执行代码回滚
                $backPath = dirname(dirname($_SERVER['DOCUMENT_ROOT']));
                $backStatus = $this->dealZip($backFile,$backPath);
                //回滚失败
                if (!$backStatus){
                    //记录日志
                    $this->writeLog("网站回滚失败");
                    return ['code'=>0,'message'=>"网站回滚失败！"];
                }
                //记录日志
                $this->writeLog("网站回滚完成，删除升级包，备份包！");
                //删除升级包
                delete_dir_file($downPath);
                //删除备份包
                delete_dir_file($backupPath);
                //结束程序执行
                exit();
            }
        }
        //判断是否需要更新数据库，需要则备份数据库并更新
        if ($config['is_sql']){
            //记录日志
            $this->writeLog("开始数据库备份！");
            //进行数据库备份
            $sqlBackupStatus = $this->backupSql($backupPath,$system['version']);
            if (!$sqlBackupStatus){
                //删除升级包
                delete_dir_file($downPath);
                //删除备份包
                delete_dir_file($backupPath);
                //记录日志
                $this->writeLog("数据库备份失败！");
                return ['code'=>0,'message'=>"数据库备份失败！"];
            }
            //记录日志
            $this->writeLog("数据库备份完成，开始更新数据库！");
            //判断数据库文件是否存在
            if (!file_exists($downPath."/"."mysql.sql")){
                //记录日志
                $this->writeLog("数据库文件不存在,无法执行数据库更新！");
                return ['code'=>0,'message'=>"数据库文件不存在,无法执行数据库更新！"];
            }
            //动态获取数据库配置信息
            $db = Db::connect();
            //读取数据库文件语句
            $sql = file_get_contents($downPath."/"."mysql.sql");
            //字符串转数组
            $sqlArr = explode(";", trim($sql));
            //遍历数组删除为空的元素
            foreach($sqlArr as $k=>$v){
                if($v == ""){
                    //执行删除
                    unset($sqlArr[$k]);
                }
            }
            //执行语句
            foreach ($sqlArr as $query) {
                $db->query(trim($query));
            }
            //记录日志
            $this->writeLog("数据库更新完成！");
        }
        //记录日志
        $this->writeLog("删除升级包,备份包！");
        //删除升级包
        delete_dir_file($downPath);
        //删除备份包
        delete_dir_file($backupPath);
        //记录日志
        $this->writeLog("更新系统版本号！");
        //更新系统版本号
        $res = Db::name('system')->where('id',1)->update(['version'=>$version]);
        if ($res){
            //记录日志
            $this->writeLog("系统版本号更新完成！");
            return true;
        }else{
            return false;
        }
    }

    /* 备份代码
    * @$url 压缩包所在路径,包括文件名称以及后缀.zip
    */
    public function backupCode($url)
    {
        //实例化这个PclZip类
        $zip = new \PclZip($url);
        //将网站目录压缩
        $backAll = $zip->create(dirname($_SERVER['DOCUMENT_ROOT']), PCLZIP_OPT_REMOVE_PATH, dirname(dirname($_SERVER['DOCUMENT_ROOT'])));
        if($backAll){
            return true;
        }else{
            return false;
        }
    }

    /* 备份数据库
     * @$path 备份数据库文件路径
     * @$name 定义备份数据库文件名称
     */
    public function backupSql($path,$name)
    {
        $sql = new \org\Baksql(Config::get("database."),$path,$name);
        //备份处理
        $info = $sql->backup();
        if($info){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 下载程序压缩包文件
     * @param $url 要下载的url
     * @param $save_dir 要存放的目录
     * @return res 成功返回下载信息 失败返回false
     */
    function downFile($url, $save_dir)
    {
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            return false;
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir.= '/';
        }
        $filename = basename($url);
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }
        //开始下载
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        // 判断执行结果
        if ($status['http_code'] ==200) {
            $size = strlen($content);
            //文件大小
            $fp2 = @fopen($save_dir . $filename , 'a');
            fwrite($fp2, $content);
            fclose($fp2);
            unset($content, $url);
            $res = [
                'status' =>$status['http_code'] ,
                'file_name' => $filename,
                'save_path' => $save_dir . $filename
            ];
        } else {
            $res = false;
        }
        return $res;
    }

    /**
     * 解压缩
     * @param $file 要解压的文件
     * @param $todir 要存放的目录
     * @return  成功返回true 失败返回false
     */
    public function dealZip($file,$todir)
    {
        if (trim($file) == '') {
            return false;
        }
        if (trim($todir) == '') {
            return false;
        }
        $zip = new \ZipArchive;
        // 中文文件名要使用ANSI编码的文件格式
        if ($zip->open($file) === TRUE) {
            //提取全部文件
            $zip->extractTo($todir);
            $zip->close();
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * 获取文件内容
     * @param $url 要获取的url
     * @return res 成功返回内容 失败返回false
     */
    public function getFile($url)
    {
        if (trim($url) == '') {
            return false;
        }
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'timeout'=>3,//单位秒
            )
        );
        $cnt=0;
        while($cnt<3 && ($res=@file_get_contents($url, false, stream_context_create($opts)))===FALSE) $cnt++;
        if ($res === false) {
            return false;
        } else {
            return $res;
        }
    }

    /*
     * 获得目录下的所有文件路径并复制到指定的目录下面
     * $old_dir：目标文件目录
     * $new_dir：需要复制到的文件目录
     */
    public function copyFile($old_dir,$new_dir)
    {
        //判断有没有目录，没有则创建
        if(!is_dir($new_dir)){
            @mkdir($new_dir,true);
        }
        $res = '';
        //返回指定目录中的文件和目录的数组
        $temp = scandir($old_dir);
        if(is_array($temp) && count($temp)>2){
            unset($temp[0],$temp[1]);
            foreach($temp as $key=>$val){
                $file_url=$old_dir.DIRECTORY_SEPARATOR.$val;
                //组件新的目录
                $xin_dir = $new_dir.DIRECTORY_SEPARATOR.$val;
                //是否是目录
                if(is_dir($file_url)){
                    $res = $this->copyFile($file_url,$xin_dir);
                }elseif(is_file($file_url)){
                    $res = copy($file_url,$xin_dir);
                }
            }
        }
        return $res;
    }

    /**
     * [write_log 写入日志]
     * @param  [type] $data [写入的数据]
     */
    public function writeLog($data){
        $time = date('Y-m-d');
        //设置路径目录信息
        $url = $_SERVER['DOCUMENT_ROOT'].'/update/log/'.$time.'/'.'update_log.log';
        //返回目录部分
        $dir_name = dirname($url);
        //目录不存在就创建
        if(!file_exists($dir_name)){
            //iconv防止中文名乱码
            mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
        }
        //打开文件资源通道 不存在则自动创建
        $fp = fopen($url,"a");
        //写入文件
        fwrite($fp,date("Y-m-d H:i:s").var_export($data,true)."\r\n");
        //关闭资源通道
        fclose($fp);
    }
}