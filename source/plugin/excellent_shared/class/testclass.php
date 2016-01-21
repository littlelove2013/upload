<?php
	require_once("DiscuzDataTree.class.php");
	$tree=DataTree::getInstance();
	//$tree->getDataArray();
	//$tree->getOneTreeNode();
	/*
	echo "<br/>showTree<p/>";
	$tree->showTree();
	echo "<br/>Json:<p/>";
	$json_array=$tree->getOneTreeNodeJosn();
	echo $json_array."<p/>";
	$obj=json_decode($json_array);
	print_r($obj);
	echo "<p/>";
	echo "type_id:".$obj->type_id;
	foreach($obj->child as $value){
		echo "type_name:".$value->type_name."<br/>";
	}
	*/
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
	echo "<br/>new json:<br/>";
	$txt_newJson=json_decode($new_json);
	//print_r($txt_newJson);
	$tree->updateDataTreeDB($new_json);
	echo "<br/>showTree:<p/>";
	$tree->showTree(1);
?>