<?php
	/*
	* 设置微信菜单
	*/
	// 调用微信测试号全局配置
	include_once("./access_token.php");
	//echo $arr['access_token'];
	$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$arr['access_token'];
	$menu=' {
     "button":[
     {	
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单",
           "sub_button":[
           {	
               "type":"view",
               "name":"百度一下",
               "url":"https://wap.baidu.com/"
            },
            {
               "type":"view",
               "name":"我的世界",
               "url":"http://info.winner6.com/"
            },
			{
               "type":"view",
               "name":"my github",
               "url":"https://github.com/jiaowoxiaoqingba/"
            }]
       }]
 }';
	// curl模拟请求
	function https_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	$result=https_request($url,$menu);
	print_r($result);
?>