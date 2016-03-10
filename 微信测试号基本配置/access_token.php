<?php
	/*
	* 获取access_token
	*/
	// 调用微信测试号全局配置
	include_once("./allocation.php");
	$arr = json_decode(file_get_contents($file,true),true);
	if($arr['time']<time()){
		// 获取access_token地址（GET）
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appID&secret=$appsecret";
		// 获取结果
		$arr=file_get_contents($url,true);
		// access_token过期时间
		$arr=json_decode($arr,true);
		$arr['time']=time()+$arr['expires_in'];
		// 存储到文本
		file_put_contents($file,json_encode($arr));
	}
?>