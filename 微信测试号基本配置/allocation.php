<?php
	/*
	*	微信测试号全局配置
	*/
	$appID = "xxxxxxxxxxxxxxxx";
	$appsecret = "xxxxxxxxxxxxxxxx";
	$URL = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
	$Token = "xxxxxxxxxxxxxxxx";
	$domain = "xxxxxxxxxxxxxxxx";
	// 设置保存access_token文件
	$file='xxxxxxxxxxxxxxxx/access_token.txt';
	if(!file_exists($file)){
		touch("$file");
		chmod("$file", 0777);
	}
?>