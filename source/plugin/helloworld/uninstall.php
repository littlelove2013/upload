<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}
//各种安装操作  
$sql = "show tables";  
runquery($sql);  
//或  
DB::query($sql);  
  
$finish = TRUE;  

?>  
?>