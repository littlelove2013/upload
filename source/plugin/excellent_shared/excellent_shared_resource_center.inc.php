<?php
if(!defined('IN_DISCUZ')){
    exit('Access Denied');
}

require_once("class/DiscuzDataTree.class.php");
require_once("class/TypeArray.class.php");
//获取type_id数组，如果未给出type_id数组，则直接获取根节点id
require_once("class/ThreadWithType.class.php");
require_once ("function/function_showThreadWithTable.php");
require_once ("function/function_showAttachmentDownload.php");
require_once ("function/function_getAttachType.php");
$type_id=$_POST[type_id];
if(empty($type_id)) {
    $type_id=array();
    $datatree=DataTree::getInstance();
    $type_id[]=$datatree->getOneTreeNode()->type_id;//获取根节点的type_id
}
$result=ThreadWithType::getThreadSFun($type_id);
//第二个参数为显示风格，0为panel显示风格，1为table显示风格
showThreadWithTable($result,0);
?>