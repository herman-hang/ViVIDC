<?php
/**
 * 应用公共文件
 * 应用公共函数文件，函数不能定义为public类型，
 * 如果我们要使用我们定义的公共函数，直接在我们想用的地方直接调用函数即可。
 * by:小航 QQ:11467102
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


    /**
     * 公共发送邮件函数
     * $email登录邮箱, $emailpaswsd安全码/授权码, $smtp邮箱的服务器地址, $sll端口,
     * $emname发件人昵称, $title邮件主题, $content邮件内容, $toemail收件人邮箱
     * */
    function sendEmail($email,$emailpaswsd,$smtp,$sll,$emname,$title,$content,$toemail)
    {
        $mail = new PHPMailer(true);// Passing `true` enables exceptions
        try {
            //设定邮件编码
            $mail->CharSet ="UTF-8";
            // 调试模式输出
            $mail->SMTPDebug = 0;
            // 使用SMTP
            $mail->isSMTP();
            // SMTP服务器
            $mail->Host = $smtp;
            // 允许 SMTP 认证
            $mail->SMTPAuth = true;
            // SMTP 用户名  即邮箱的用户名
            $mail->Username = $email;
            // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->Password = $emailpaswsd;
            // 允许 TLS 或者ssl协议
            $mail->SMTPSecure = 'ssl';
            // 服务器端口 25 或者465 具体要看邮箱服务器支持
            $mail->Port = $sll;
            //发件人
            $mail->setFrom($email, $emname);
            // 收件人
            $mail->addAddress($toemail);
            // 可添加多个收件人
            //$mail->addAddress('ellen@example.com');
            //回复的时候回复给哪个邮箱 建议和发件人一致
            $mail->addReplyTo($toemail, $emname);
            //抄送
            //$mail->addCC('cc@example.com');
            //密送
            //$mail->addBCC('bcc@example.com');

            //发送附件
            // $mail->addAttachment('../xy.zip');// 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');// 发送附件并且重命名

            // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->isHTML(true);
            $mail->Subject = $title;
            $mail->Body    = $content;
            $mail->AltBody = '当前邮件客户端不支持HTML，请用浏览器登录邮箱查看内容！';
            // 发送邮件 返回状态
            $status = $mail->send();
            return $status;
        } catch (Exception $e) {
            echo '邮件发送失败: ', $mail->ErrorInfo;
        }
    }

    /**
     * 短信宝短信发送
     * $user短信宝账号, $passMD5加密的密码, $content短信内容, $phone手机号码
     */
    function sendSms($user,$pass,$content,$phone)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        return $statusStr[$result];
    }
    /**
     * 循环删除目录和文件
     * @param string $dir_name
     * @return bool
     */
    function delete_dir_file($dir_name)
    {
        $result = false;
        if(is_dir($dir_name)){
            if ($handle = opendir($dir_name)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != '.' && $item != '..') {
                        if (is_dir($dir_name . DIRECTORY_SEPARATOR. $item)) {
                            delete_dir_file($dir_name . DIRECTORY_SEPARATOR. $item);
                        } else {
                            unlink($dir_name . DIRECTORY_SEPARATOR. $item);
                        }
                    }
                }
                closedir($handle);
                if (rmdir($dir_name)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * 订单号生成
     * @return string
     */
    function trade_no()
    {
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr(str_replace('0.', '', $usec), 0 ,4);
        $str  = rand(10,99);
        return date("YmdHis").$usec.$str;
    }

    /**
     * 生成6位随机验证码
     * $type 验证码类型，1为邮件验证码，2为短信验证码
     * 类型正确返回验证码,否则false
     * */
    function codeStr($type = "")
    {
        //邮件验证码
        if ($type == 1){
            $arr=array_merge(range('a','z'),range('A','Z'),range('0','9'));
            shuffle($arr);
            $arr=array_flip($arr);
            $arr=array_rand($arr,6);
            $res='';
            foreach ($arr as $v){
                $res.=$v;
            }
            return $res;
        }elseif($type == 2){//短信验证码
            $arr=array_merge(range('0','9'));
            shuffle($arr);
            $arr=array_flip($arr);
            $arr=array_rand($arr,6);
            $res='';
            foreach ($arr as $v){
                $res.=$v;
            }
            return $res;
        }
        return false;
    }
