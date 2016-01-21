<?php
/**
 * Created by PhpStorm.
 * User: 龚成
 * Date: 2016/1/20
 * Time: 19:46
 */
//由字符串获取格式代表图片
function getAttachType($type)
{
    $type=strtolower($type);

        if(in_array($type,array(".jpg",".bmp",".png")))
            return "jpg.jpg";
        if( in_array($type,array(".rar",".zip",".exe",".jar")))
            return "rar.jpg";
        if( in_array($type,array(".txt",".text",".pdf",".xml",".doc",".docx")))
            return"text.jpg";
       return "unknown.jpg";

}

?>