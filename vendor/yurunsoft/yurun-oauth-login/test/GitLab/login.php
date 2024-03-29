<?php

require __DIR__ . '/common.php';
$gitlabOAuth = new \Yurun\OAuthLogin\GitLab\OAuth2($GLOBALS['oauth_gitlab']['appid'], $GLOBALS['oauth_gitlab']['appkey'], $GLOBALS['oauth_gitlab']['callbackUrl']);

// 解决只能设置一个回调域名的问题，下面地址需要改成你项目中的地址，可以参考./loginAgent.php写法
// $gitlabOAuth->loginAgentUrl = 'http://localhost/test/GitLab/loginAgent.php';

// 所有为null的可不传，这里为了演示和加注释就写了
$url = $gitlabOAuth->getAuthUrl(
    null,	// 回调地址，登录成功后返回该地址
    null,	// state 为空自动生成
    null	// scope 只要登录默认为空即可
);
$_SESSION['YURUN_GITLAB_STATE'] = $gitlabOAuth->state;
header('location:' . $url);
