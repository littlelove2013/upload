<?php
	/**
	 *
	 *  excellent_setting.inc.php 2016-1-5 龚成
	 */
	 
	if(!defined('IN_DISCUZ')){
		exit('Access Denied');
	}
	
	///$gc_path=DISCUZ_ROOT."config";
	//echo $gc_path;
	//首先获取分类信息并存储在一个结构体内
	//首先获取root的位置
	require_once ("DB.class.php");
	class DataTreeNode{
		//本节点数据
		public $father_type_id;
		public $type_id;
		public $type_name;
		public $type_level;
		public $child_type_id;
		//孩子节点数组
		public $child;
		function __construct($father_type_id=NULL,$type_id=0,$type_name=NULL,$type_level=0,$child_type_id=array(),$child=array()){
			$this->father_type_id=$father_type_id;
			$this->type_id=intval($type_id);
			$this->type_name=$type_name;
			$this->type_level=intval($type_level);
			$this->child_type_id=$child_type_id;
			$this->child=$child;
		}
	}
	class DataTree{
		private static $_instance;
		public $root;
		private $debug;
		private $mysqli;
		private $tablepre;
		private $db_config;//数据库
		private $ID;//定义未插入ID(是一个极大不可取值)
		//存储方式为array({$type_id}=>{$type_data}),便于直接获取对应id的内容
		public $data_array;
		function __construct($config=NULL){
			$this->debug=false;
			$this->ID=65535;
			if($config==NULL){
				global $gc_path;
				//echo realpath($path);
				//echo $gc_path."\\config_global.php";
				//echo DISCUZ_ROOT."<br/>";
				include(DISCUZ_ROOT."config\\config_global.php");
				//print_r($_config);
				if(!empty($_config)){
					$this->db_config=$_config['db']['1'];
				}else{
					echo "获取数据库配置失败！<br/>";
				//echo DISCUZ_ROOT."..<br/>";
					return ;
				}
			}else{
				$this->db_config=$config['db']['1'];
			}
			$this->tablepre=$this->db_config['tablepre'];
			$this->openMysql();
			$this->getRoot();
			$this->getAllChild($this->root);
			$this->closeMysql();
		}
		private function openMysql(){
			//$this->mysqli=new MySQLi("localhost:3306","root","159753159753","gcdiscuzforum");
			$this->mysqli=new MySQLi($this->db_config['dbhost'],$this->db_config['dbuser'],$this->db_config['dbpw'],$this->db_config['dbname']);
			//$this->mysqli->connect("localhost:3306","root","159753159753","gcdiscuzforum") or die("连接失败1	");
			//$this->mysqli->select_db("gcdiscuzforum") or die("连接失败2");
			//mysqli_select_db($this->connect,"gcdiscuzforum") or die("连接失败2");
		}
		private function closeMysql(){
			$this->mysqli->close();
		}
		private function query($sql){
			if(empty($this->mysqli)){
				$this->openMysql();
			}
			$sqldata=$this->mysqli->query($sql);
			if (!$sqldata) {
				var_dump($sqldata);
    			return false;
				//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			}
			return $sqldata;
		}
		//开始一个事务
		private function start_transaction(){
			//若链接关闭，则打开连接
			if(empty($this->mysqli)){
				$this->openMysql();
			}
			$this->mysqli->autocommit(false);
		}
		//事务提交
		private function commit_transaction(){
			$this->mysqli->commit();
			if($this->debug){
				echo "提交成功！<br/>";
			}
			$this->mysqli->autocommit(true);
		}
		//事务回滚
		private function rollback_transaction(){
			$this->mysqli->rollback();
			if($this->debug){
				echo "提交失败，事务回滚！<br/>";
			}
			$this->mysqli->autocommit(true);
		}
		//获取根节点
		private function getRoot(){
			//初始化数组
			$this->data_array=array();
			$sql = "SELECT `type_id`,`type_name` FROM `{$this->tablepre}forum_gc_type_thread` WHERE `type_level`=0";
			//$sqldata=DB::query($sql);
			$sqldata=$this->mysqli->query($sql);
			if (!$sqldata) {
				var_dump($sqldata);
    			throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			}
			//echo "NUM:".mysqli_num_rows($sqldata);
			//$data=DB::fetch($sqldata);
			//print_r($data=$sqldata->fetch_assoc());
			$data=$sqldata->fetch_assoc();
			//echo  "DATA::".$data['type_id']." ".$data['type_name']."<br/>";
			$this->root=new DataTreeNode(NULL,$data['type_id'],$data['type_name'],0);
			$this->data_array[$data['type_id']]=$this->root;
		}
		//递归获取特子节点的所有后代节点
		private function getAllChild(&$data_node){
			$sql = "SELECT b.father_type_id AS 'father_type_id',"
    			. "	c.type_id AS 'type_id',c.type_name as 'type_name', "
    			. " c.type_level AS 'type_level' "
    			. "FROM `{$this->tablepre}forum_gc_type_thread` a "
    			. "	INNER JOIN `{$this->tablepre}forum_gc_type_relation` b ON a.type_id=b.father_type_id "
    			. " LEFT JOIN `{$this->tablepre}forum_gc_type_thread` c ON b.child_type_id=c.type_id "
				. " WHERE a.type_id=".$data_node->type_id.";";
			//$sqldata=DB::query($sql);
			$sqldata=$this->mysqli->query($sql);
			if (!$sqldata) {
				var_dump($sqldata);
    			throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			}
			//$num=DB::num_rows($sqldata);
			//$num=$sqldata->num_rows;
			//$trans=0;
			//得到各子节点
			//while($data=DB::fetch($sqldata)){
			while($data=$sqldata->fetch_assoc()){
				//echo  "DATA::".$data["father_type_id"]."  " .$data["type_id"]."  ".$data["type_name"]."  ".$data["type_level"]."<br/>";
				$data_node->child[$data['type_id']]=new DataTreeNode($data_node->type_id,$data['type_id'],$data['type_name'],$data["type_level"]);
				//数组存储
				$this->data_array[$data['type_id']]=$data_node->child[$data['type_id']];
				//递归获得所有属于该子节点的后代节点
				$this->getAllChild($data_node->child[$data['type_id']]);
				$data_node->child_type_id[$data['type_id']]=$data["type_id"];
				//$trans+=1;
			}
		}
		public function showTree($type=0){
			$str="|——";
			echo "TreeData:<br/>";
			if($type==0)
				$this->showNode($this->root,$str);
			if($type==1){
				echo "<table class='table table-hover table-bordered table-condensed table-striped'>
						<caption>DataTree</caption>";
				$this->showNodeTable($this->root);
				echo "</table>";
			}
			echo "dataToarray:<br/>";
			//print_r($this->data_array);
			$this->showDataArray($this->data_array,$str);
		}
		private function showNode($data,$str=""){
			if(empty($data)){
				return;
			}
			//echo $str."<input type='text' name='{$data->type_name}' value='{$data->type_name}' /><br/>";
			echo $str."{".$data->father_type_id.",".$data->type_id.",".$data->type_name.",".$data->type_level."};<br/>";
			$str.="——|——";
			foreach($data->child as $value){
				$this->showNode($value,$str);
			}
		}
		private function showNodeTable($data){
			if(empty($data)){
				return;
			}
			$i=$data->type_level;
			while($i>0){
				$str.="&nbsp;&nbsp;&nbsp;";
				$i--;
			}
			$tableline="
				<tr>
					<td> <input type='checkbox'/> </td>
					<td> {$str}<span class='glyphicon glyphicon-folder-open'>&nbsp;{$data->type_name}</span> </td>
					<td align='center'><a href='#'>查看</a>|<a href='#'>删除</a></td> 
				</tr>
			";
			echo $tableline;
			//echo $str."{".$data->father_type_id.",".$data->type_id.",".$data->type_name.",".$data->type_level."};<br/>";
			foreach($data->child as $value){
				$this->showNodeTable($value);
			}
		}
		private function showDataArray($data,$str=""){
			if(empty($data)){
				return;
			}
			$str.="————>";
			foreach($data as $key=>$value){
				echo "[".$key."]".$str."{".$value->father_type_id.",".$value->type_id.",".$value->type_name.",".$value->type_level."};<br/>";
				
			}
		}
		//获取制定type_id指向的树节点
		public function getOneTreeNode($type_id=NULL){
			if($type_id==NULL) return $this->root;
			return $this->data_array[$type_id];
		}
		
		//获取type_id指定的节点的及其所有子节点type_id，返回type_id数组
		public function getOneTreeNodeAllTypeid($type_id=NULL){
			$node=$this->getOneTreeNode($type_id);
			$type_id_array=array();
			//$node->child;
			$this->getOneTreeNodeOneTypeid($type_id_array,$node);
			return $type_id_array;
		}
		private function getOneTreeNodeOneTypeid(&$type_id_array,$node){
			$type_id_array[]=$node->type_id;
			foreach($node->child as $value){
				$this->getOneTreeNodeOneTypeid($type_id_array,$value);
			}
		}
		//获取某节点的第level层父节点
		//输入参数为type_id:查找的起始节点
		//        	ancesterlevel:查找的目的节点所在层(值域为1到type_level-1)
		public function getANodeByOtherNode($type_id,$ancesterlevel)
		{
			//检查参数
			if(!is_numeric($type_id)||!is_numeric($ancesterlevel)||$ancesterlevel<=0){
				return array(false,"getANodeByOtherNode：参数错误:请输入数值型参数");
			}
			$type_id=intval($type_id);
			$ancesterlevel=intval($ancesterlevel);
			//获取type_id指定的节点
			$typenode=$this->data_array[$type_id];
			if(empty($typenode)||$typenode->type_level<$ancesterlevel) {
				return array(false, "getANodeByOtherNode：参数错误:只能查找其祖先节点");
			}

			while($typenode->type_level!=$ancesterlevel && $typenode->father_type_id!=NULL){
				$typenode=$this->data_array[$typenode->father_type_id];
			}
			if($typenode->type_level==$ancesterlevel) {
				return array(true, $typenode);
			}else{
				return array(false,"getANodeByOtherNode：未查找到指定层祖先节点");
			}
		}
		//获取全局数组
		public function getDataArray(){
			return $this->data_array;
		}
		//获取json字符串
		public function getOneTreeNodeJson($type_id=NULL){
			if($type_id==NULL) return json_encode($this->root);
			return json_encode($this->getOneTreeNode($type_id));
		}
		/**
     	+----------------------------------------------------------
     	* 由输入的json字符串
		* 更新所有的菜单到数据库
     	+----------------------------------------------------------
     	* 
     	* @access public
     	+----------------------------------------------------------
     	* @return NULL
     	+----------------------------------------------------------
     	*/
		public function updateDataTreeDB($json_menu){
			
			//开始数据库连接
			$this->openMysql();
			//开始一个事务
			$this->start_transaction();
			//插入或者更新菜单
			$exist_array=$this->insertAllMenu($json_menu);
			if(!$exist_array){
				//插入失败
				$this->rollback_transaction();
				return false;
			}
			if($this->debug){
				echo "<br/>line:".__LINE__.":输出提交菜单的结构：<br/>";
				print_r($exist_array);
				echo "<br/>";
			}
			//删除多余菜单
			if(!($this->deleteAllExtraMenu($exist_array))){
				//删除失败
				$this->rollback_transaction();
				return false;
			}
			//提交事务
			$this->commit_transaction();
			//重新获取数据库菜单
			unset($this->root);
			$this->data_array=array();
			unset($this->data_array);
			$this->getRoot();
			$this->getAllChild($this->root);
			
			//关闭数据库连接
			$this->closeMysql();
			return true;
		}
		/**
     	+----------------------------------------------------------
     	* 插入所有的菜单
     	+----------------------------------------------------------
     	* 
     	* @access private
     	+----------------------------------------------------------
     	* @return NULL
     	+----------------------------------------------------------
     	*/
		//插入所有的菜单
		private function insertAllMenu($json_menu){
			$root=json_decode($json_menu);
			//$root=new DataTreeNode();
			//若不是从根目录开始插入，则返回错误并停止
			if($root==NULL || $root->type_level != 0){
				echo "出错！<br/>";
				print_r($root);
				exit;
			}
			//初始化已存在数组id,用于返回值，作为删除多余菜单依据
			$exist_array=array();
			//开始插入数据
			$father_type;
			$insertflag=$this->insertOneMenu($father_type,$root,$exist_array);
			if($insertflag)
				return $exist_array;
			else
				return false;
		}
		//菜单插入数据库
		//$root是由json字符串解析成的对象
		//$father_type为父节点
		//插入$root节点及其与父节点关系
		private function insertOneMenu(&$father_type,$root,&$exist_array){
			//$root=new DataTreeNode();
			if(empty($root) || $root->type_name==NULL){
				//若$root为空，则说明插入到最底部，直接返回
				//若名字为空，则不操作直接返回
				return false;
			}
			$type_id;//记录该节点的type_id;
			if($this->debug && !empty($father_type)){
				echo "<br/>line:".__LINE__.":父子节点关系：<br/>";
				echo "father_type_name:".$father_type->type_name."<br/>";
				echo "father_type_id:".$father_type->type_id."<br/>";
				echo "child_type_name:".$root->type_name."<br/>";
				echo "child_type_id:".$root->type_id."<br/>";
			}
			/*
			if($root->type_level==0 && ($root->father_type_id!=NULL||$root->type_name!='root')){
				//非根节点level为0则退出
				echo "目录设置错误";
				exit;
			}
			*/
			//若类型数组没有记录该id，或者该ID为极大值，则该id为新添加，需要写入数据库
			//检查父节点数组
			if((empty($this->data_array[$root->type_id]) && $root->type_id >= $this->ID) && !empty($father_type)){
				//先检查该父节点下是否已经存在相同名字的子节点
				$sql = "SELECT c.type_id as 'type_id' "
    				. " FROM `{$this->tablepre}forum_gc_type_relation` b "
    				. "	LEFT JOIN `{$this->tablepre}forum_gc_type_thread` c ON b.child_type_id=c.type_id "
    				. " WHERE b.father_type_id=".$father_type->type_id." AND c.type_name='".$root->type_name."';";
				$sqldata=$this->mysqli->query($sql);
				if($this->debug){
					echo __LINE__.":关于数据存在性检测<br/>";
					//sql
					echo $sql."<br/>";
					//输出变量
					var_dump($sqldata);
					echo "<br/>";
				}
				$row=$sqldata->fetch_assoc();
				//为空，则表示不存在同名，可以添加
				if(empty($row)){
					if($this->debug){
						echo __LINE__.":关于插入节点：<br/>插入新节点：".$root->type_name."<br/>";
					}
					$sql = "INSERT INTO `{$this->tablepre}forum_gc_type_thread`(`type_name`,`type_level`) VALUES ('".$root->type_name."',".$root->type_level.")";
					$sqldata=$this->mysqli->query($sql);
					if (!$sqldata) {
						var_dump($sqldata);
    					return false;
						//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
					}
					//如果没错，则获取插入节点的id
					$type_id=$this->mysqli->insert_id;
					$root->type_id=$type_id;
					//插入本节点与父节点关系
					if($this->debug){
						echo "插入新边：".$father_type->type_id."————".$root->type_id."<br/>";
					}
					$sql = "INSERT INTO `{$this->tablepre}forum_gc_type_relation`(`father_type_id`,`child_type_id`)VALUES(".$father_type->type_id.",".$type_id.")";
					$sqldata=$this->mysqli->query($sql);
					if (!$sqldata) {
						var_dump($sqldata);
    					return false;
						//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
					}
					//添加到对象
					//$father_type->child->{$root->type_id}=$root;
					//添加到数组
					//$this->data_array[$root->type_id]=$root;
				}else{
					//若能在同意自菜单中找到同名菜单，则更新该新菜单的type_id;
					$type_id=$row['type_id'];
				}
			}else{
				//若名称不一样，则说明需要修改数据库
				if($this->debug){
					echo __LINE__.":关于数据更新：<br/>data_array[$root->type_id]:<br/>";
					print_r($this->data_array[$root->type_id]);
					echo "<br/>data_array[root->type_id]->type_name:{$this->data_array[$root->type_id]->type_name}<br/>";
					echo "root->type_name:{$root->type_name}";
				}
				if(!empty($this->data_array[$root->type_id]) && $this->data_array[$root->type_id]->type_name != $root->type_name){
					if($this->debug) echo "更改数据库:<br/>";
					$sql = "UPDATE `{$this->tablepre}forum_gc_type_thread` SET `type_name`='".$root->type_name."' WHERE `type_id`=".$root->type_id.";";
					$sqldata=$this->mysqli->query($sql);
					if (!$sqldata) {
						var_dump($sqldata);
    					return false;
						//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
					}
					//更新到对象
					//$father_type->child->{$root->type_id}=new stdClass();//新建空累
					//$father_type->child->{$root->type_id}->type_name = $root->type_name;
					//更新到数组
					//$this->data_array[$root->type_id]->typename = $root->type_name;
				}
				$type_id=$root->type_id;
			}
			//若不为空且名称未变，则说明是原菜单，则不做修改
			//更新已存在数组
			if($this->debug){
				echo "<br/>line:".__LINE__."更新存在菜单数组:<br/>";
				echo "type_id:".$type_id."<br/>";
			}
			$exist_array[$type_id]=1;
			$root->type_id=$type_id;
			$isright=true;
			//递归插入其子节点
			foreach($root->child as $value){
				//检查level是否符合规则
				if(($value->type_level - $root->type_level) != 1){
					//若子节点的level不比父节点大1，则提示出错，并停止运行
					echo "目录层次设置错误";
					//回滚事务
					return false;
					//退出
					//exit;	
				}
				$isright&=$this->insertOneMenu($root,$value,$exist_array);
			}
			return $isright;
		}
		/**
     	+----------------------------------------------------------
     	* 删除多余菜单
     	+----------------------------------------------------------
     	* 
     	* @access private
     	+----------------------------------------------------------
     	* @return NULL
     	+----------------------------------------------------------
     	*/
		//删除原来多余菜单（在做完插;入之后进行的）
		private function deleteAllExtraMenu($exist_array){
			return $this->deleteOneExtraMenu($this->root,$exist_array);
		}
		//$exist_array表示现在存在的菜单，用于和原有菜单进行对比，进行原菜单多余删除
		private function deleteOneExtraMenu(&$root,$exist_array){
			if(!empty($root)){
				//若该节点菜单不存在，则直接删除，然后返回
				if(!$exist_array[$root->type_id]){
					return $this->deleteOneNode($root);
				}
				$rightflag=true;
				//若存在，则检查其子节点
				foreach($root->child as $value){
					$rightflag&=$this->deleteOneExtraMenu($value,$exist_array);
				}
				return $rightflag;
			}else{
				return false;
			}
			//为空则直接返回
		}
		//递归删除一个节点函数
		private function deleteOneNode(&$node){
			if(empty($node)) return;//为空则直接返回null
			//先删除其所有子节点
			$rightflag=true;
			foreach($node->child as $value){
				$rightflag&=$this->deleteOneNode($value);
			}
			//若子类任意一个删除错误，则直接返回错误，不进行自身删除
			if(!$rightflag){
				return false;
			}
			//再删除本节点
			$sql = "DELETE FROM `{$this->tablepre}forum_gc_type_thread` WHERE `type_id`=".$node->type_id.";";
			$sqldata=$this->mysqli->query($sql);
			if (!$sqldata) {
				var_dump($sqldata);
    			return false;
				//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			}
			//都正确，则返回true;
			return true;
			//更新到对象
			//unset($node);
			//$node=NULL;
			//更新到数组
			//unset($this->data_array[$node->type_id]);
			//$this->data_array[$node->type_id]=NULL;
		}
		/* 按类型名搜索类型
		 * 反回值为type_id的数组
		 * 返回形式为array(true/false,vale)
		 */
		public static  function searchType($type_name){
			if(!is_string($type_name)){
				return array(false,"函数参数错误");
			}
			//查找数据库，获取type_id数组
			$type_name=trim($type_name);
			$db=GCDB::getInstance();
			$sql="SELECT type_id FROM `{$db->tablepre}forum_gc_type_thread` WHERE `type_name` LIKE '%{$type_name}%';";
			$sqldata=$db->query($sql);
			if(!$sqldata){
				return array(false,"数据库查询错误");
			}
			$result=array();
			while($row=$sqldata->fetch_assoc()){
				$result[]=intval($row['type_id']);
			}
			return array(true,$result);
		}
		/**
     	+----------------------------------------------------------
     	* 取得数据库类实例
     	+----------------------------------------------------------
     	* @static
     	* @access public
     	+----------------------------------------------------------
     	* @return mixed 返回数据库驱动类
     	+----------------------------------------------------------
     	*/
    	public static function getInstance($config=NULL)
    	{
			if ( self::$_instance==null ){
				self::$_instance = new DataTree($config);
			}
			return self::$_instance;
    	}
	}
    
    ?>