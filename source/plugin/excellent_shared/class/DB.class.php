<?php
	 /**
	 *
	 *  excellent_setting.inc.php 2016-1-13 龚成
	 */
	if(!defined('IN_DISCUZ')){
		exit('Access Denied');
	}
	if(!defined(GC_CONFIG_PATH)){
		define('GC_CONFIG_PATH',DISCUZ_ROOT."config");
	}
	//echo "GC_CONFIG_PATH:".GC_CONFIG_PATH."<br/>";
	class GCDB{
		public $mysqli;
		private static $_instance;
		private $debug;
		public $tablepre;
		private $db_config;//数据库
		function __construct($config=NULL){
			$this->debug=false;
			$this->get_config($config);
		}
		private function get_config($config=NULL){
			if($this->debug){
				echo "设置数据库配置参数(DB.class.php>>get_config())<br/>";
			}
			if($config==NULL){
				//global $gc_path;
				//echo realpath($path);
				//echo $gc_path."\\config_global.php";
				//如果配置为空，则查找配置文件
				include(DISCUZ_ROOT."config\\config_global.php");
				//print_r($_config);
				if(!empty($_config)){
					$this->db_config=$_config['db']['1'];
				}else{
					echo "获取数据库配置失败！<br/>";
				//echo DISCUZ_ROOT."..<br/>";
					return false;
				}
			}else{
				$this->db_config=$config['db']['1'];
			}
			$this->tablepre=$this->db_config['tablepre'];
			if($this->debug){
				echo "数据库配置参数成功(DB.class.php>>get_config())<br/>";
			}
			return true;
		}
		public function query($sql){
			if($this->debug){
				echo "<font color='orange'>运行sql语句(DB.class.php>>query())</font><br/>";
			}
			if(empty($this->mysqli)){
				$this->openMysql();
			}
			$sqldata=$this->mysqli->query($sql);
			if (!$sqldata) {
				var_dump($sqldata);
				if($this->debug)
					echo "<font color='Red'>".__LINE__.":sql:".$sql."</font><br/>";
    			return false;
				//throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			}
			return $sqldata;
		}
		public function getInsertID(){
			if($this->debug){
				echo "获取插入数据库ID(DB.class.php>>getInsertID())<br/>";
			}
			return $this->mysqli->insert_id;
		}
		private function openMysql(){
			if($this->debug){
				echo "<font color='orange'>打开数据库连接(DB.class.php>>openMysql())</font><br/>";
			}
			//$this->mysqli=new MySQLi("localhost:3306","root","159753159753","gcdiscuzforum");
			//若配置为空，则再载入一次
			if(empty($this->db_config)){
				$this->get_config();
			}
			if(empty($this->db_config)){
				echo "<font color='orange'>配置文件错误！</font>";
				exit;
			}
			$this->mysqli=new MySQLi($this->db_config['dbhost'],$this->db_config['dbuser'],$this->db_config['dbpw'],$this->db_config['dbname']);
			$this->query("SET NAMES {$this->db_config['dbcharset']}");
			if($this->debug){
				echo "<font color='green'>打开数据库连接成功(DB.class.php>>openMysql())</font><br/>";
			}
			return true;
			//$this->mysqli->connect("localhost:3306","root","159753159753","gcdiscuzforum") or die("连接失败1	");
			//$this->mysqli->select_db("gcdiscuzforum") or die("连接失败2");
			//mysqli_select_db($this->connect,"gcdiscuzforum") or die("连接失败2");
		}
		private function closeMysql(){
			$this->mysqli->close();
		}
		//开始一个事务
		public function start_transaction(){
			//若链接关闭，则打开连接
			if(empty($this->mysqli)){
				$this->openMysql();
			}
			$this->mysqli->autocommit(false);
		}
		//事务提交
		public function commit_transaction(){
			//若链接关闭，则打开连接
			if(empty($this->mysqli)){
				echo "<font color='red'>".__LINE__.":未连接或连接已关闭!</font><br/>";
				return false;
			}
			$this->mysqli->commit();
			if($this->debug){
				echo "<font color='lime'>提交成功！</font><br/>";
			}
			$this->mysqli->autocommit(true);
			return true;
		}
		//事务回滚
		public function rollback_transaction(){
			if(empty($this->mysqli)){
				echo __LINE__.":未连接或连接已关闭!<br/>";
				return false;
			}
			$this->mysqli->rollback();
			if($this->debug){
				echo "提交失败，事务回滚！<br/>";
			}
			$this->mysqli->autocommit(true);
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
				self::$_instance = new GCDB($config);
			}
			return self::$_instance;
    	}
	}
?>