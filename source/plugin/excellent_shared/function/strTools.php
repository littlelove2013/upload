<?php
$debug = false;
	//对数据进行urlcode代码转换
	function wphp_urlencode($data){
		if(is_array($data)||is_object($data)){
			foreach($data as $key=>$value){
				if(is_scalar($value)){//$value是不是标量（非数组和对象）
					if(is_array($data)){
						$data[$key] = urlencode($value);
					}else{
						$data->$key = urlencode($value);
					}
				}else{
					//递归调用
					if(is_array($data)){
						$data[$key] = wphp_urlencode($value);
					}else{
						$data->$key = wphp_urlencode($value);
					}
				}
			}
		}
		return $data;
	}
	//防止json_encode()函数在编排汉字时自动对所有汉子进行unicode编码
	function ch_json_encode($data){
		$ret = wphp_urlencode($data);
		$ret = json_encode($ret);
		return urldecode($ret);
	}
if($debug){
	echo "<br>这里是strTools.php内json编码测试：<br>";
	//测试
	$data = array('button'=>array(
					array(
					'name'=>'今日热门',
					'type'=>'click',
					'key'=>'00102364'),
					array(
					'name'=>'浏览记录',
					'type'=>'click',
					'key'=>'00102365'),
					array(
					'menu'=>array(
						array(
						'name'=>'登录',
						'type'=>'click',
						'key'=>'00102365'),
						array(
						'name'=>'注册',
						'type'=>'click',
						'key'=>'00102365'),
						))			
					)
				);
	echo "源数据：<br>";
	print_r($data);
	echo "<br>ch_json_encode()编码后：<br>";
	$str1 = ch_json_encode($data);
	echo "ch_json:".$str1."<p>";
	echo "<br>php自带json_encode()编码后：<br>";
	$str2 = json_encode($data);
	echo "json:".$str2."<p>";
}
?>