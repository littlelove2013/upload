<?php
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

?>