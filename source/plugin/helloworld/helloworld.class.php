<?php
	if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
	}	
	class plugin_helloworld{
		function common(){  
        	global $_G;  
        	if($_G['uid']){  
            //经验值加1点  
        	}  
   		}  
		function global_footer(){
			$query= DB::query("SELECT * FROM ".DB::table('plugin_helloworld').";");

			$hw_field=DB::fetch($query);

			$hw_set_saystring=$hw_field['say_string'];
			
			global $_G;
			
			return "{$_G[username]},{$hw_set_saystring}";
            
		}
	}
	/*
	class plugin_helloworld_member extends plugin_helloworld{  
      
    	function register_top(){  
        	header('location:http://zc.qq.com/chs/index.html'); //引导用户去注册QQ号  
        	exit;  
    	} 
	 
	} */
?>