<?php

require dirname(__DIR__) . '/common.php';
$GLOBALS['PAY_CONFIG'] = [
    'appid'			        => '',
    'mch_id'		        => '',
    'key'			          => '',
    'pay_notify_url'	 => 'http://yurun.test.com/test/Weixin/pay_notify.php',
    'certPath'	       => __DIR__ . '/cert/apiclient_cert.pem',
    'keyPath'	        => __DIR__ . '/cert/apiclient_key.pem',
];
