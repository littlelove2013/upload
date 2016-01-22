<?php

	/**
 *
 *  excellent_setting.inc.php 2016-1-5 龚成
 */
	if(!defined('IN_DISCUZ')){
		exit('Access Denied');
	}
	
	if(defined('DISCUZ_ROOT')){
		$path=DISCUZ_ROOT."source\\plugin\\excellent_shared\\";
	}
	
	require_once($path."class/DiscuzDataTree.class.php");
require_once($path."function/function_makeCache.php");
require_once("class/TypeArray.class.php");
	$tree=DataTree::getInstance();
	$new_json = <<<EOT
		{	"father_type_id":null,
			"type_id":"37",
			"type_name":"root",
			"type_level":0,
			"child_type_id":{
				"0":"65535",
				"1":"65535"
			},
			"child":{
				"0":{
					"father_type_id":"37",
					"type_id":"65535",
					"type_name":"gongcheng",
					"type_level":"1",
					"child_type_id":{
						"65535":"65535"
					},
					"child":{
						"0":{
							"father_type_id":"65535",
							"type_id":"65535",
							"type_name":"firstmenu",
							"type_level":"2",
							"child_type_id":[],
							"child":[]
						},
						"1":{
							"father_type_id":"65535",
							"type_id":"65535",
							"type_name":"secondmenu",
							"type_level":"2",
							"child_type_id":[],
							"child":[]
						}
					}
				},
				"1":{
					"father_type_id":"37",
					"type_id":"65535",
					"type_name":"default",
					"type_level":"1",
					"child_type_id":[],
					"child":{
						"0":{
							"father_type_id":"65535",
							"type_id":"65535",
							"type_name":"firstmenu",
							"type_level":"2",
							"child_type_id":[],
							"child":[]
						},
						"1":{
							"father_type_id":"65535",
							"type_id":"65535",
							"type_name":"secondmenu",
							"type_level":"2",
							"child_type_id":[],
							"child":[]
						}
					}
				}
			}
		}
EOT;
	//$tree->updateDataTreeDB($new_json);
	
	
	$muilt_array=TypeArray::getInstance();
	$muilt_array->showArray();
	echo $muilt_array->getArrayJson();
	$json_muilt_menu=<<<EOP
	{"30":{"0":{"type_id":"65535","type_name":"PPT","type_level":"30"},"60":{"type_id":"65535","type_name":"WORD","type_level":"30"},"61":{"type_id":"65535","type_name":"XML","type_level":"30"}},"31":{"62":{"type_id":"65535","type_name":"语文","type_level":"31"},"63":{"type_id":"65535","type_name":"数学","type_level":"31"}}}
EOP;
	//$muilt_array->updateDataArrayDB($json_muilt_menu);
	//测试获取当前URL
	require_once("function/function_getCurrentURL.php");
	echo "<br/>currentURL:".curPageURLWithFactor()."<br/>";
	//测试获取附件URL
	require_once("class/ThreadWithType.class.php");
	//$gc_attach=new GCAttachment(9,1,1,1,1452828687,"ac7547829c69fc9b0df4d23c.jpg",111082,"201601/15/113127yg5g6icztg55qt6z.jpg");
	//测试抓取附件
/*
	$gc_attach=new GCAttachment(6,4,4);
	$gc_attach->showAttachment();
	print_r(GCAttachment::getAidTableidByTid(1));
	echo "<br/>";
	//测试获取帖子信息
	$gc_threadnode=new GCThreadNode(4);
	$gc_threadnode->showThreadNode();
	$ThreadWithAttach=$gc_threadnode;
	include template('excellent_shared/attachment.tpl');
*/
	//测试类型帖子

	print_r(ThreadWithType::insertThreadTypeSFun(1,array("37","58"),NULL));
	print_r(ThreadWithType::insertThreadTypeSFun(2,array("56","58"),"心情舒畅"));
	echo "<br/>";
	$data=array("37");
	$type_thread=new ThreadWithType($data);
	$result=ThreadWithType::getThreadSFun($data);
	if($result[0]) {
		$type_thread->showThread($result[1]);
	}
	//$type_thread->deleteThreadType(2);
	//测试静态函数
	//ThreadWithType::insertThreadTypeSFun(2,array("56","60"),"心情舒畅");
	//ThreadWithType::deleteThreadTypeSFun(2);
	$threadwithtype=$type_thread->getThreadArray();
$test=ThreadWithType::beCategoried(1);
if($test[0]==-1){
	echo "出错<br/>";
}
if($test[0]==0	) {
	echo "该帖子未分类<br/>";
}else{
	print_r($test);
}
//print_r($threadwithtype);
	echo "帖子分类操作部分完成<br/>";
	require_once ("function/function_showThreadWithTable.php");
	require_once ("function/function_showAttachmentDownload.php");
	//showThreadWithTable($threadwithtype);
	include template("excellent_shared/threadwithtype.tpl");
	//$type_thread->deleteThreadType("2");
	//print_r($type_thread->getJsonThread());
	echo "<br/>";
	//echo $gc_attach->getAttachmentURL()."<br/>";
	//print_r(json_decode($txt));
	
	
	//print_r($obj->getOneTreeNode());
	/*
	$obj->showTree();
	$txt=$obj->getOneTreeNodeJosn();
	echo $txt."<p>";
	print_r($obj->getOneTreeNode()); echo "<p>";
	$path="D:/text1.txt";
	$fw=fopen($path,'w');
	fwrite($fw,$txt);
	fclose($fw);
	$obj2=json_decode($txt);
	print_r($obj2);
	*/
	//include template('excellent_shared/type_setting');
	include DISCUZ_ROOT.'./data/cache/plugin_'.$identifier.'.php';
	$data_array=$tree->getDataArray();
	$data_root=$tree->getOneTreeNode();
	$value=$data_root->type_id;
	$name=$data_root->type_name;
	$tree->showTree(1);
	//echo DB::table();
	//echo dirname(__FILE__);
	//echo $gc_path;
	//print_r($_G['config']['db']['1']);
	echo "<font color=#FF0004'>测试文件运行完成</font>";
	//include template("excellent_shared/excellent_setting");
	/*
	function getOneTypeTreeNodeFormDB($type_id){
		$type_tree=array();
		$sql = "SELECT `type_id`,`type_name` FROM `gcdiscuzforum_forum_gc_type_thread` WHERE `type_level`=0";
		$sqldata=DB::query($sql);
		$data=DB::fetch($sqldata);
		$trans=0;
		$type_tree[$trans]=new DataTreeNode(NULL,$data['type_id'],$data['type_name'],0);
		//获取子节点
		$sql = "SELECT b.father_type_id AS \'father_type_id\',\n"
    . "	c.type_id AS \'type_id\',c.type_name as \'type_name\', \n"
    . " c.type_level AS \'type_level\' \n"
    . "FROM `gcdiscuzforum_forum_gc_type_thread` a \n"
    . "	INNER JOIN `gcdiscuzforum_forum_gc_type_relation` b ON a.type_id=b.father_type_id \n"
    . " LEFT JOIN `gcdiscuzforum_forum_gc_type_thread` c ON b.child_type_id=c.type_id \n"
	. " WHERE a.type_id=".$type_tree[$trans].type_id;
		$sqldata=DB::query($sql);
		$num=DB::num_rows($sqldata);
		while($num!=0){
			while($data=DB::fetch($sqldata)){
				echo  $data["father_type_id"]."  " .$data["type_id"]."  ".$data["type_name"]."  ".$data["type_level"]."<br/>";
				$trans+=1;
				$type_tree[$trans]=new DataTreeNode(NULL,$data['type_id'],$data['type_name'],0);
			}
		}
		
	}
	$type_tree=array();
	$sql = "SELECT `type_id`,`type_name` FROM `gcdiscuzforum_forum_gc_type_thread` WHERE `type_level`=0";
	$sqldata=DB::query($sql);
	$data=DB::fetch($sqldata);
	$trans=0;
	$type_tree[$trans++]=new DataTreeNode($data['type_id'],$data['type_name']);
	*/
	
?>