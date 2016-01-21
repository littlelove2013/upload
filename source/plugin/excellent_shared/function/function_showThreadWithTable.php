<?php
/**
 * Created by PhpStorm.
 * User: 龚成
 * Date: 2016/1/19
 * Time: 19:48
 */
    //$threadwithtype数据为GCThreadNode对象数组
    require_once ("function_getCurrentURL.php");
    function showThreadWithTable($threadwithtype,$type){
        $gc_current_URL=curPageURL();
        //获取最后一个/之前得所有字符串
        $gc_current_URL=strchr($gc_current_URL,"/",true);
        if($type) {
            include template("excellent_shared/threadwithtype.tpl");
        }else {
            include template("excellent_shared/threadwithtype2.tpl");
        }
    }
?>