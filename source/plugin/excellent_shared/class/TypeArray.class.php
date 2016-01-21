<?php
	//本文件实现取除树结构之外的并行分类，其level皆在30以上，实现多重类别的获取，插入，和删除
	require_once("DB.class.php");
	class DataNode{
		public $type_id;
		public $type_name;
		public $type_level;
		function __construct($type_id=0,$type_name=NULL,$type_level=0){
			$this->type_id=$type_id;
			$this->type_name=$type_name;
			$this->type_level=$type_level;
		}
		
	}
	class TypeArray{
		private static $_instance;
		public $root;
		private $debug;
		private $db;
		private $ID;//定义未插入ID(是一个极大不可取值)
		//存储方式为array({$type_id}=>{$type_data}),便于直接获取对应id的内容
		public $data_array;
		function __construct($config=NULL){
			$this->debug=false;
			$this->ID=65535;
			$this->root=array();
			//$this->openMysql();
			$this->db=GCDB::getInstance($config);
			$this->getArray();
			
		}
		//获取level>=30的所有数组，并且按level分组
		private function getArray(){
			if($this->debug){
				echo "获取多重菜单(TypeArray.class.php>>getArray())<br/>";
			}
			$level=30;
			$sql="SELECT * FROM `{$this->db->tablepre}forum_gc_type_thread` WHERE type_level>=30 ORDER BY type_level ASC;";
		 	$sqldata=$this->db->query($sql);
			if(!$sqldata){
				if($this->debug){
					echo "<font color='#FB0004>获取多重菜单失败(TypeArray.class.php>>getArray())</font><br/>";
				}
				return false;
			}
			$this->root[$level]=array();
			while(($row=$sqldata->fetch_assoc())){
				if($this->debug){
					print_r($row);
					echo "<br/>";
				}
				echo "<br/>";
				//获取每一组的值
				if($row['type_level']==$level){
					$this->root[$level][$row['type_id']]=new DataNode($row['type_id'],urlencode($row['type_name']),$row['type_level']);
				}else{
					$level+=1;//自增1
					if($row['type_level']==$level){
						$this->root[$level]=array();
						$this->root[$level][$row['type_id']]=new DataNode($row['type_id'],urlencode($row['type_name']),$row['type_level']);
					}else{
						//说明后面没有更多分类，因此直接跳出循环
						break;
					}
				}
			}
			if($this->debug){
				echo "获取多重菜单成功(TypeArray.class.php>>getArray())<br/>";
			}
			//全部获取，则返回true；
			return true;
		}
		public function showArray(){
			foreach($this->root as $keys=>$values){
				echo "level:".$keys.":<br/>";
				foreach($values as $key=>$value){
					echo "&nbsp;&nbsp;&nbsp;type_id:".$key.":".urldecode($value->type_name)."<br/>";
				}
			}
		}
		public function getDataArray($level=NULL){
			if($level==NULL) return $this->root;
			return array($level=>$this->root[$level]);
		}
		public function getArrayJson($level=NULL){
			if($level==NULL) return urldecode(json_encode($this->root));
			return urldecode(json_encode(array($level=>$this->root[$level])));
		}
		//获取指定level的所有子节点type_id数组
		public function getArrayTypeid($level){
			if($level==NULL){
				echo "level参数缺失<br/>";
				return false;
			}
			$data=$this->root[$level];
			$typearray=array();
			foreach($data as $key=>$value){
				$typearray[]=$key;
			}
			return $typearray;
		}
		//插入多重菜单，需要注意，多重菜单采用json字符串格式输入
		//json格式为：1、以level作为第一维数组的key，必须以30起始，连续不中断，同一level菜单在同一level下的数组，不允许数据不一致
		//			 2、以type_id作为第二维数组的key，若为新添加菜单则需要将其标记为65535，key值为递增即可，表示新添加菜单
		//			 3、第三维为DataNode对象，此处会检查第一层的level和第二层type_id是否正确
		public function updateDataArrayDB($json_menu){
			if($this->debug){
				echo "<font color='orange'>更新多重菜单(TypeArray.class.php>>updateDataArrayDB())</font><br/>";
			}
			$insertdata=json_decode($json_menu);
			if(empty($insertdata)){
				//说明插入错误
				echo "json格式错误！";
				if($this->debug){
					echo "<font color='red'>更新多重菜单失败(TypeArray.class.php>>getArray())</font><br/>";
				}
				return false;
			}
			if($this->debug){
				echo "<br/>";
				print_r($insertdata);
				echo "<p/>";
			}
			//开始事务
			$this->db->start_transaction();
			//插入操作
			$exist_array=$this->insertMuiltMenu($insertdata);
			if(!$exist_array){
				if($this->debug){
					echo "<font color='red'>更新多重菜单失败(TypeArray.class.php>>updateDataArrayDB())</font><br/>";
				}
				//回滚
				$this->db->rollback_transaction();
				return false;
			}
			if($this->debug){
				echo "exist_array:";
				print_r($exist_array);
				echo "<br/>";
			}
			//删除多余菜单
			if(!$this->deleteAllExtraMenu($exist_array)){
				if($this->debug){
					echo "<font color='red'>更新多重菜单失败(TypeArray.class.php>>updateDataArrayDB())</font><br/>";
				}
				//回滚
				$this->db->rollback_transaction();
				return false;
			}
			//事务提交
			$this->db->commit_transaction();
			if($this->debug){
				echo "<font color='green'>更新多重菜单成功(TypeArray.class.php>>updateDataArrayDB())</font><br/>";
			}
			//重新从数据库获取数据
			$this->getArray();
			return true;
		}
		private function insertMuiltMenu($data_array){
			if($this->debug){
				echo "<font color='orange'>插入多重菜单(TypeArray.class.php>>insertMuiltMenu())</font><br/>";
			}
			$exist_array=array();
			$level=30;
			if(empty($data_array->$level)){
				return false;
			}
			while(!empty($data_array->$level)){
				$exist_array[$level]=array();
				foreach($data_array->$level as $key=>$value){
					$type_id==NULL;
					if($value->type_level != $level){
						return false;
					}
					//说明是待插入节点
					if($value->type_id>=$this->ID && $value->type_name != NULL){
						//首先需要查找该层级下是否已经存在同名菜单，若存在，则不插入，否则插入
						$sql="SELECT `type_id` FROM `{$this->db->tablepre}forum_gc_type_thread` WHERE `type_name`='{$value->type_name}' AND `type_level`={$level}";
						
						$sqldata=$this->db->query($sql);
						$row=$sqldata->fetch_assoc();
						if($this->debug){
							echo "<font color='OrangeRed'>".__LINE__.":查找该层是否已经存在同名菜单";
							print_r($row);
							echo "</font><br/>";
						}
						if(!empty($row)){
							if($this->debug){
								echo "<font color='OrangeRed'>说明已存在该菜单，不可插入</font><br/>";
							}
							$exist_array[$level][$row['type_id']]=1;
						}else{//做插入操作
							if($this->debug){
								echo "<font color='green'>说明不存在存在该菜单，进行插入操作</font><br/>";
							}
							$sql="INSERT INTO `{$this->db->tablepre}forum_gc_type_thread`(`type_name`,`type_level`) VALUES ('{$value->type_name}',{$level});";
							if(!$this->db->query($sql)){
								echo "<font color='Red'>".__LINE__.":插入数据库失败！</font><br/>";
								return false;
							}
						}
					}else{
					//说明是存在节点，做了修改或者没做修改
						if(!empty($this->root[$level][$value->type_id])){
							if($this->debug){
								echo "<font color='Orange'>修改菜单</font><br/>";
							}
							if($value->type_name != $this->root[$level][$value->type_id]->type_name){
								//更新数据库
								$sql="UPDATE `{$this->db->tablepre}forum_gc_type_thread` SET `type_name`='{$value->type_name}' WHERE `type_id`={$value->type_id};";
								if(!$this->db->query($sql)){
									echo "<font color='Red'>".__LINE__.":更新数据库失败！</font><br/>";
									return false;
								}
								$exist_array[$level][$value->type_id]=1;
							}
						}else{
							echo "<font color='Red'>json格式错误，请修改后重新插入！</font><br/>";
							return false;
						}
					}
				}
				//下一层
				$level+=1;
			}
			if($this->debug){
				echo "<font color='green'>插入多重菜单成功(TypeArray.class.php>>insertMuiltMenu())</font><br/>";
			}
			return $exist_array;
		}
		private function deleteAllExtraMenu($exist_array){
			if($this->debug){
					echo "<font color='orange'>删除多余菜单(TypeArray.class.php>>deleteAllExtraMenu())</font><br/>";
			}
			foreach($this->root as $level=>$values){
				foreach($values as $type_id=>$datanode){
					//若$exist_array[$level][$type_id]为空，则表示该菜单已经被删除，则执行删除操作
					if(empty($exist_array[$level][$type_id])){
						$sql="DELETE FROM `{$this->db->tablepre}forum_gc_type_thread` WHERE `type_id`={$type_id}";
						if(!$this->db->query($sql)){
							echo "删除操作失败！<br/>";
							if($this->debug){
								echo "<font color='red'>删除多余菜失败单(TypeArray.class.php>>deleteAllExtraMenu())</font><br/>";
							}
							return false;
						}
					}
				}
			}
			if($this->debug){
				echo "<font color='green'>删除多余菜单成功(TypeArray.class.php>>deleteAllExtraMenu())</font><br/>";
			}
			return true;
		}
		public static function getInstance($config=NULL)
    	{
			if ( self::$_instance==null ){
				self::$_instance = new TypeArray($config);
			}
			return self::$_instance;
    	}
	}
?>