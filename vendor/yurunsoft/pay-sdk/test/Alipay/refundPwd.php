<?php
/**
 * 支付宝有密退款Demo.
 */
require __DIR__ . '/common.php';

// 公共配置
$params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
$params->appID = $GLOBALS['PAY_CONFIG']['appid'];
$params->md5Key = $GLOBALS['PAY_CONFIG']['md5Key'];
// $params->appPrivateKey = $GLOBALS['PAY_CONFIG']['privateKey'];
// $params->appPrivateKeyFile = ''; // 证书文件，如果设置则这个优先使用

// SDK实例化，传入公共配置
$pay = new \Yurun\PaySDK\Alipay\SDK($params);

// 支付接口
$request = new \Yurun\PaySDK\Alipay\Params\RefundPwd\Request();
$request->notify_url = $GLOBALS['PAY_CONFIG']['notify_url']; // 支付后通知地址（作为支付成功回调，这个可靠）
$request->businessParams->seller_user_id = $GLOBALS['PAY_CONFIG']['appid']; // 卖家支付宝用户号
$request->businessParams->refund_date = date('Y-m-d H:i:s'); // 退款请求时间
$request->businessParams->batch_no = date('Ymd') . mt_rand(100, 99999999); // 退款批次号
$request->businessParams->batch_num = 1; // 总笔数
$request->businessParams->detail_data = '2017081521001004640269135539^0.01^协商退款'; // 单笔数据集

// 跳转到支付页面
// $pay->redirectExecute($request);

// 获取跳转url
$pay->prepareExecute($request, $url);
var_dump($url);
