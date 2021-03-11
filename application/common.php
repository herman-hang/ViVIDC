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
     * 获取文件内容
     * @param $url 要获取的url
     * @return res 成功返回内容 失败返回false
     */
    function get_file($url)
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