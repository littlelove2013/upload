<?php
if(!defined('IN_DISCUZ')){
    exit('Access Denied');
}
if($_G[uid]==0) {
    //showmessage('not_loggedin', null, 'NOPERM');
    showmessage('请选登录', '', array(), array('login' => true));
}
require_once("class/DiscuzDataTree.class.php");
require_once("class/TypeArray.class.php");
//获取type_id数组，如果未给出type_id数组，则直接获取根节点id
require_once("class/ThreadWithType.class.php");
require_once ("function/function_showThreadWithTable.php");
require_once ("function/function_showAttachmentDownload.php");
require_once ("function/function_getAttachType.php");
$type_id=$_POST[type_id];
print_r(ThreadWithType::beCategoried(1));
if(empty($type_id)) {
    $type_id=array();
    $datatree=DataTree::getInstance();
    $type_id[]=$datatree->getOneTreeNode()->type_id;//获取根节点的type_id
}
$result=ThreadWithType::getThreadSFun($type_id)[1];
//第二个参数为显示风格，0为panel显示风格，1为table显示风格
showThreadWithTable($result,0);
?>