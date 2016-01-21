<?php
	//本文件实现多重类的帖子信息的获取，以及对帖子进行多重分类和去除多重分类
	if(!defined('IN_DISCUZ')){
		exit('Access Denied');
	}
	if(!defined('DISCUZ_ROOT')){
		exit('Access Denied');
	}
	//定义当前用户
	if(empty($_G)){
		exit('Access Denied');	
	}

	//echo "GC_CURRENT_UID:".GC_CURRENT_UID."<br/>";

	require_once("DB.class.php");
	require_once("DiscuzDataTree.class.php");
	require_once("TypeArray.class.php");
	require_once("function_strTools.php");
	if(!defined('SECURITY_AUTHKEY')){
		define('SECURITY_AUTHKEY',$_G['config']['security']['authkey']);
	}
	//echo "security_authkey:".SECURITY_AUTHKEY."<br/>";
	//帖子附件的结构
	//获取aid、tid、tableid指定的附件信息
	class GCAttachment{
		private $debug;
		public $aid;//附件id
		public $tid;//所属帖子id
		public $uid;//发表用户id
		public $tableid;//附件所属附件表
		public $dateline;//上传日期
		public $filename;//文件名称
		public $gc_filesize;//文件大小（字节）
		public $filepath;//文件路径（相对于discuz\upload\data\attachment\forum或者网络连接）
		public $downloadURL;//下载路径
		public $attachtype;//附件类型
		/*不支持函数重载
		function __construct($aid=0,$tid=0,$uid=0,$tableid=127,$dateline=0,$filename=NULL,$gc_filesize=0,$filepath=NULL){
			$this->debug=true;
			if($this->debug) echo "开始构造函数<br/>";
			$this->aid=$aid;
			$this->tid=$tid;
			$this->uid=$uid;
			$this->tableid=$tableid;
			$this->dateline=$dateline;
			$this->filename=$filename;
			$this->gc_filesize=$gc_filesize;
			$this->filepath=$filepath;
			$this->getAttachmentURL();
			$this->getAttachmentType();
			if($this->debug) echo "构造函数成功<br/>";
		}*/
		//可有aid和tid唯一确定一个附件,tableid确定要查找的表
		//$uid确定
		/**
		 * GCAttachment constructor.
		 * @param $aid
		 * @param $tid
		 * @param $tableid
         */
		function __construct($aid, $tid, $tableid){
			global $_G;
			if(empty($_G)){
				echo "构造对象失败!无法获取uid<br/>";
				exit;
			}
			$this->debug=false;
			if($this->debug) echo "开始构造函数<br/>";
			//从数据库获取数据：
			$this->aid=intval($aid);
			$this->tid=intval($tid);
			$this->uid=intval($_G['uid']);//获取定义当前用户
			$this->tableid=intval($tableid);
			//初始化初始值，从数据库获取值
			if(!$this->init()){
				echo "构造对象失败!初始化函数失败<br/>";
				exit;
			}
			if($this->debug) echo "构造函数成功<br/>";
		}
		private function init(){
			if($this->debug){
				echo "aid:{$this->aid}<br/>";
				echo "uid:{$this->uid}<br/>";
				echo "tableid:{$this->tableid}<br/>";
				echo "SECURITY_AUTHKEY:".SECURITY_AUTHKEY."<br/>";
			}
			if($this->aid==0 || $this->uid==0 || $this->tableid==127 || !defined('SECURITY_AUTHKEY')){
				echo "GCAttachment对象构造错误<br/>";
				return false;
			}
			//获取数据库数据
			$db=GCDB::getInstance();
			$tablename=$db->tablepre."forum_attachment_".$this->tableid;
			if($this->debug){
				echo "附件所在表：".$tablename."<br/>";
			}
			$sql = "SELECT dateline,
							filename,
      						filesize as 'gc_filesize',
      						attachment as 'filepath'
							FROM `{$db->tablepre}forum_attachment_{$this->tableid}`
							WHERE `aid`={$this->aid} AND `tid`={$this->tid}";
			$sqldata=$db->query($sql);
			$row=$sqldata->fetch_assoc();
			if(empty($row)){
				echo "sql:".$sql."<br/>";
				echo "GCAttachment对象构造错误:未查到该attachment_id指定的附件<br/>";
				return false;
			}
			if($this->debug){
				var_dump($sqldata);
				echo "row:";
				print_r($row);
				echo "<br/>";
			}
			$this->dateline=$row['dateline'];
			$this->filename=$row['filename'];
			$this->gc_filesize=$row['gc_filesize'];
			$this->filepath=$row['filepath'];
			//获取其他参数：
			//$this->getAttachmentURL();
			//$this->getAttachmentType();
			return ($this->getAttachmentURL())&&($this->getAttachmentType());
		}
		private function getAttachmentURL(){
			if($this->debug){
				echo "开始获取附件URL函数<br/>";
				echo $this->aid." : ".$this->uid." : ".$this->tableid." : ".SECURITY_AUTHKEY." : <br/>";
			}
			
			if($this->aid==0 || $this->uid==0 || $this->tableid==127 || !defined('SECURITY_AUTHKEY')){
				if($this->debug){
					echo "class:GCAttachment:getAttachmentURL()获取下载附件URL失败";
				}
				return false;
			}
			$t=time();
			if($this->debug) echo "t:".$t."<br/>";
			$aid=intval($this->aid);
			if($this->debug) echo "aid:".$aid."<br/>";
			$uid=intval($this->uid);
			if($this->debug) echo "uid:".$uid."<br/>";
			$k=substr(md5($aid.md5(SECURITY_AUTHKEY).$t.$uid), 0, 8);
			if($this->debug) echo "k:".$k."<br/>";
			$tableid=intval($this->tableid);
			$str=$aid."|".$k."|".$t."|".$uid."|".$tableid;
			if($this->debug) echo "str:".$str."<br/>";
			$str=urlencode(base64_encode($str));
			if($this->debug) echo "获取附件URL函数成功<br/>";
			$GC_URL=GCAttachment::curPageURL();
			$this->downloadURL=str_ireplace("plugin.php","forum.php",$GC_URL)."?mod=attachment&aid=".$str;
			return true;
		}
		private function getAttachmentType(){
			if(empty($this->filename)||$this->filename==NULL){
				echo "filename:".$this->filename."<br/>";
				echo "文件名为空！获取文件类型失败<br/>";
				return false;
			}
			$this->attachtype=strrchr($this->filename,".");
			return true;
		}
		public static function curPageURL() 
		{
    		$pageURL = 'http';
    		if ($_SERVER["HTTPS"] == "on") 
    		{
       			 $pageURL .= "s";
    		}
    		$pageURL .= "://";
			$this_page = $_SERVER["REQUEST_URI"];
   			 // 只取 ? 前面的内容
   			if (strpos($this_page, "?") !== false)
    		{
        		$this_pages = explode("?", $this_page);
        		$this_page = reset($this_pages);
    		}
    		if ($_SERVER["SERVER_PORT"] != "80") 
    		{
        		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $this_page;
    		} 
    		else 
    		{
        		$pageURL .= $_SERVER["SERVER_NAME"] . $this_page;
    		}
    		return $pageURL;
		}
		//静态函数，获取tid对应的所有aid和tableid
		//返回类型为数组，key对应aid，value对应：tableid
		public static function getAidTableidByTid($tid){
			//global $_G;
			//echo "uid:".$_G['uid'];
			//为空则直接返回错误
			if($tid==NULL) 
				return false;
			//获取数据库操作对象
			$db=GCDB::getInstance();
			$sql="SELECT `aid`,`tableid` FROM `{$db->tablepre}forum_attachment` WHERE tid={$tid};";
			$sqldata=$db->query($sql);
			if(!$sqldata){
				return false;
			}
			$data=array();
			while($row = $sqldata->fetch_assoc()){
				$data[$row['aid']]=$row['tableid'];
			}
			return $data;
		}
		public function showAttachment(){
			echo "附件类型：".$this->attachtype."<br/>";
			echo "附件大小:".ceil($this->gc_filesize/1024)."KB<br/>";
			echo "附件上传日期:".date("Y-m-d",$this->dateline)."<br/>";
			echo "附件下载链接：<a href=".$this->downloadURL.">$this->filename</a><br/>";
		}
	}
	//帖子的结构
	//获取tid指定的帖子的所有信息
	class GCThreadNode{
		private $debug;
		public $tid;
		public $author;
		public $subject;
		public $dateline;
		public $lastpost;
		public $lastposter;
		public $views;//查看次数
		public $replies;//回复次数
		public $isattach;//是否有附件
		public $attachment;//附件数组
		/*
		function __construct(
			$author=NULL,
			$subject=NULL,
			$dateline=0,
			$lastpost=0,
			$lastposter=NULL,
			$views=0,//查看次数
			$replies=0,//回复次数
			$isattach=false,//是否有附件
			$attachment=array(),//附件数组
			$rate=NULL)
		{
				$this->author=$author;
				$this->subject=$subject;
				$this->dateline=$dateline;
				$this->lastpost=$lastpost;
				$this->lastposter=$lastposter;
				$this->views=$views;//查看次数
				$this->replies=$replies;//回复次数
				$this->isattach=$isattach;//是否有附件
				$this->attachment=$attachment;//附件数组
				$this->rate=$rate;
		}
		*/
		//由帖子tid获取帖子的所有信息
		function __construct($tid){
			if($tid==NULL){
				echo "GCThreadNode：未输入构造参数<br/>";
				exit;
			}
			$this->debug=false;
			$this->tid=$tid;
			if(!$this->init()){
				echo "GCThreadNode：构造函数出错<br/>";
			}
			if($this->debug){
				echo "GCThreadNode：构造函数成功<br/>";
			}
		}
		private function init(){
			if($this->debug){
				echo "GCThreadNode：init初始化参数<br/>";
			}
			$db=GCDB::getInstance();
			$sql="
				SELECT t.author,
						t.subject,
      					t.dateline,
      					t.lastpost,
      					t.lastposter,
      					t.views,
      					t.replies,
      					t.attachment as 'isattach'
				FROM `{$db->tablepre}forum_thread` t
				WHERE t.tid={$this->tid};
				";
			$sqldata=$db->query($sql);
			$row=$sqldata->fetch_assoc();
			if(empty($row)){
				echo "指tid未搜索到数据<br/>";
				return false;
			}
			$this->author=$row['author'];
			$this->subject=$row['subject'];
			$this->dateline=$row['dateline'];
			$this->lastpost=$row['lastpost'];
			$this->lastposter=$row['lastposter'];
			$this->views=$row['views'];//查看次数
			$this->replies=$row['replies'];//回复次数
			$this->isattach=$row['isattach'];//是否有附件
			if($this->isattach){
				//如果有附件，则取附件
				$data=GCAttachment::getAidTableidByTid($this->tid);
				foreach($data as $key=>$value){
					//以aid作为数组的key值，生成附件数组
					$this->attachment[$key]=new GCAttachment($key,$this->tid,$value);
				}
			}else{
				$this->attachment=array();//附件数组
			}
			return true;
		}
		public function showThreadNode(){
			echo '<br/>主题:'.$this->subject."<br/>";
			echo "帖子ID:".$this->tid."<br/>";
			echo '作者:'.$this->author."<br/>";
      		echo '发帖时间:'.date("Y-m-d H:i:s",$this->dateline)."<br/>";
      		echo '最后修改:'.date("Y-m-d H:i:s",$this->lastpost)."<br/>";
     		echo '最后编辑人:'.$this->lastposter."<br/>";
      		echo '浏览次数:'.$this->views."<br/>";
      		echo '回复次数:'.$this->replies."<br/>";
      		echo '有无附件:';
			if($this->isattach){
				$conut=count($this->attachment);
				echo "<font color='lime'>有{$conut}个附件：</font><br/>";
				//输出附件
				echo "<font color='#DE05FC'>";
				foreach($this->attachment as $value){
					$value->showAttachment();
				}
				echo "</font><p/>";
			}else{
				echo "<font color='red'>无附件：</font><p/>";
			}
		}
	}
	//获取type_id数组指定的所有帖子
	//1、如果type_id是对应的树结构(level<30),则还要获取其所有子节点的
	//		方法是，先获取其所有子节点的type_id，然后用或(OR)连接
	//2、如果type_id是对应的多重类别，则和树结构id共同限定
	//		方法：获取或有type_level>=30的type_id和1中树结构，各搜索数据库然后取交集
	//			但是因为mysql的数据库不支持取交集操作，所以改为对所有取值进行取内连接（INNER JOIN）
	//3、type_id数组形式为array("type_id"=>"type_level");
	class ThreadWithType{
		private $debug;
		//分类id=>level(必须用id来区分各分类，因为分类名可重复)数组
		//形式为array("type_id"=>"type_level");
		public $type;
		public $type_id;
		private $db;//存储数据库操作对象
		private $result;//存储获取的结果
		function __construct($type_id){
			if(empty($type_id)||!is_array($type_id)){
				echo "type_id数组为空！设置错误<br/>";
				exit;
			}
			$this->debug=false;
			if($this->debug){
				echo "开始ThreadWithType构造函数<br/>";
			}
			$this->db=GCDB::getInstance();
			//进行初始化
			/*
			if(!$this->init($type_id)){
				echo "ThreadWithType初始化函数错误<br/>";
				exit;
			}
			*/
			$this->type_id=$type_id;
			//获取该类型的帖子集合
			if(!$this->getThreadWithType()){
				echo "ThreadWithType获取该类型帖子错误<br/>";
				exit;
			}
			if($this->debug){
				echo "ThreadWithType构造函数完成<br/>";
			}
		}
		/*
		//初始化type数组，使其构成type_id=>type_level的形式
		private function init($type_id){
			if(!is_array($type_id)){
				echo "type_id参数非数组形式！参数错误<br/>";
				return false;
			}
			$this->type=$this->getType($type_id);
			//print_r($this->type);
			return true;
		}*/
		//获取指定的type数组（不检查type_id数组）
		private function getType($type_id){
			$onlyonetree=0;
			//循环获取type_id对应的level
			foreach($type_id as $value){
				if(!is_numeric($value)){//判断value是否为数字
					echo "type_id数组值非法！参数错误<br/>";
					return false;
				}
				$sql="SELECT `type_id`,`type_level` FROM `{$this->db->tablepre}forum_gc_type_thread` WHERE type_id={$value};";
				$sqldata=$this->db->query($sql);
				$row=$sqldata->fetch_assoc();
				//数据库不存在该类型id
				if(empty($row)){
					echo "数据库不存在该type_id，请不要编造！<br/>";
					return false;
				}
				//获取level，设置$this->type数组
				$type[$row['type_id']]=$row['type_level'];
				if($row['type_level']<30){
					$onlyonetree+=1;
				}
			}
			if($onlyonetree!=1){
				echo "有且只能有一个树节点的分类！分类参数错误！<br/>";
				return false;
			}
			return $type;
		}
		
		//类内函数，获取type_id数组指定的帖子数据
		private function getThreadWithOldType(){
		//1、获取该类下所有的tid
			//此变量用于查找数据库的临时表的临时变量
			if(empty($this->type)){
				echo "type数组为空<br/>";
				return false;
			}
			$tmptablenum=ord('A');
			$tmptablechr=chr($tmptablenum);
			$totalsql="
				SELECT DISTINCT {$tmptablechr}.tid \n
				FROM `{$this->db->tablepre}forum_gc_excellent_thread` as {$tmptablechr} \n";//此处记得留空格
			//获取树节点及其子节点的分类
			//写sql语句
			foreach($this->type as $type_id=>$type_level){
				$sql="";
				$tmpoftmptablechr=$tmptablechr;
				$tmptablenum+=1;
				$tmptablechr=chr($tmptablenum);
				if($type_level<30){//说明是树节点
					//获取树节点即其所有子节点
					$tree=DataTree::getInstance();
					//获取type_id指定的节点
					$treetypeid=$tree->getOneTreeNodeAllTypeid($type_id);
					//获取所有节点id
					if($this->debug){
						echo "树节点的所有子节点：<br/>";
						print_r($treetypeid);
						echo "<br/>";
					}
					//写sql语句
					$sql="SELECT tid FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE 0";//此处有空格
					foreach($treetypeid as $value){
						$sql.=" OR type_id={$value}";
					}
					//没有分号
				}else{
					//多重分类，直接写
					$sql="SELECT tid FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE type_id={$type_id}";
				}
				//组装总的sql语句
				$totalsql.="INNER JOIN \n (".$sql.") as {$tmptablechr} ON {$tmpoftmptablechr}.tid={$tmptablechr}.tid \n";//留空格
			}
			$totalsql.=";";
			//输出sql语句
			if($this->debug){
				echo "sql语句：<br/>".$totalsql."<br/>";
			}
		//2、得到sql语句之后，执行获得所有的tid，再根据tid获得所有的帖子实例，进行输出
			$sqldata=$this->db->query($totalsql);
			if(!$sqldata){
				echo "sql语句出错<br/>";
				return false;
			}
			$tid=array();
			echo "num:".mysqli_num_rows($sqldata)."<br/>";
			while($row=$sqldata->fetch_assoc()){
				$tid[]=$row['tid'];
				if($this->debug){
					echo "row:{$row['tid']}<br/>";
				}
			}
			if(empty($tid)){
				echo "未找到符合分类的帖子<br/>";//即使没有帖子，也应该返回true
				return true;
			}
			if($this->debug){
				echo "tid:<br/>";
				print_r($tid);
				echo "<br/>";
			}
			//获取tid指定的所有帖子
			foreach($tid as $value){
				$this->result[$value]=new GCThreadNode($value);
				//$this->result[$value]->showThreadNode();
			}
			return true;
		}
		//类内函数，获取type_id数组指定的帖子数据
		//不检查level
		private function getThreadWithType(){
			//1、获取该类下所有的tid
			//此变量用于查找数据库的临时表的临时变量
			$tmptablenum=ord('A');
			$tmptablechr=chr($tmptablenum);
			$totalsql="
				SELECT DISTINCT {$tmptablechr}.tid \n
				FROM `{$this->db->tablepre}forum_gc_excellent_thread` as {$tmptablechr} \n";//此处记得留空格
			//获取树节点及其子节点的分类
			//写sql语句
			foreach($this->type_id as $type_id){
				$sql="";
				$tmpoftmptablechr=$tmptablechr;
				$tmptablenum+=1;
				$tmptablechr=chr($tmptablenum);
				//说明是树节点
					//获取树节点即其所有子节点
					$tree=DataTree::getInstance();
					//获取type_id指定的节点
					$treetypeid=$tree->getOneTreeNodeAllTypeid($type_id);
					//获取所有节点id
					if($this->debug){
						echo "树节点的所有子节点：<br/>";
						print_r($treetypeid);
						echo "<br/>";
					}
					//写sql语句
					$sql="SELECT tid FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE 0";//此处有空格
					foreach($treetypeid as $value){
						$sql.=" OR type_id={$value}";
					}
					//没有分号
				//组装总的sql语句
				$totalsql.="INNER JOIN \n (".$sql.") as {$tmptablechr} ON {$tmpoftmptablechr}.tid={$tmptablechr}.tid \n";//留空格
			}
			$totalsql.=";";
			//输出sql语句
			if($this->debug){
				echo "sql语句：<br/>".$totalsql."<br/>";
			}
			//2、得到sql语句之后，执行获得所有的tid，再根据tid获得所有的帖子实例，进行输出
			$sqldata=$this->db->query($totalsql);
			if(!$sqldata){
				echo "sql语句出错<br/>";
				return false;
			}
			$tid=array();
			//echo "num:".mysqli_num_rows($sqldata)."<br/>";
			while($row=$sqldata->fetch_assoc()){
				$tid[]=$row['tid'];
				if($this->debug){
					echo "row:{$row['tid']}<br/>";
				}
			}
			if(empty($tid)){
				echo "未找到符合分类的帖子<br/>";//即使没有帖子，也应该返回true
				return true;
			}
			if($this->debug){
				echo "tid:<br/>";
				print_r($tid);
				echo "<br/>";
			}
			//获取tid指定的所有帖子
			foreach($tid as $value){
				$this->result[$value]=new GCThreadNode($value);
				//$this->result[$value]->showThreadNode();
			}
			return true;
		}
		//展示该类型的所有帖子
		public function showThread($result=NULL){
			if($result==NULL){
				$result=$this->result;
			}
			if(empty($result)){
				//echo "没有帖子可以显示<br/>";
				return array(false,"没有帖子可以显示");
			}
			//输出该类型的所有帖子
			foreach($result as $value){
				//print_r($value);
				echo "<br/>";
				$value->showThreadNode();
				echo "<br/>";
			}
		}
		//获取json字符串格式
		public function getJsonThread(){
			return ch_json_encode($this->result);
		}
		//获取结果
		public function getThreadArray(){
			return $this->result;
		}
		//帖子分类函数
		//输入参数为$tid(int变量)，$type_id（数组）,$rate（string变量）数组
		public function insertThreadOldType($tid,$type_id,$rate){
			if($this->debug){
				echo "开始insertThreadType函数<br/>";
			}
			//参数检查
			if(empty($tid)||!is_array($type_id)||empty($type_id)){
				echo "参数错误<br/>";
				return false;
			}
			//检查评论项,更改其格式（为空则置NULL，为str则加上单引号）
			if($rate==NULL||!is_string($rate)){
				$rate="NULL";
			}else{
				$rate="'{$rate}'";
			}
			//获取type的level，构造type数组(type_id=>type_level)
			$type=$this->getType($type_id);
			if(!$type){
				echo "请检查type_id参数";
				return false;
			}
			if($this->debug){
				echo "构造type数组<br/>";
			}
			//检查数据库是否已经存在该分类
			$sql="";
			foreach($type_id as $key=>$value){
				$sql="SELECT count(*) as 'num' FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND type_id={$value};";
				$sqldata=$this->db->query($sql);
				$row=$sqldata->fetch_assoc();
				if($row['num']>0){
					//说明已存在该类关系，则去掉数组值
					unset($type_id[$key]);
					unset($type[$value]);
				}
			}
			if(empty($type)){
				if($this->debug)
					echo "该分类关系已经存在<br/>";
				return true;
			}
			//上面函数已经检查好参数
			//先进行数据库查重
			//构造sql语句
			$sql="";
			//开始事务
			$this->db->start_transaction();
			//数据库查重
			foreach($type as $id=>$level){
				$sql="DELETE FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND ( 0 ";//此处加空格,直接删除同类型的tid联系
				if($level<30){
					//是树节点，需要查找其所有的子节点，看是否有重复
					$tree=DataTree::getInstance();
					//获取其所有子节点id
					//参数为空则是获取所有节点
					$childid_array=$tree->getOneTreeNodeAllTypeid();
					foreach($childid_array as $value){
						$sql.=" OR type_id={$value} ";
					}
					
				}else{
					//是多重分类，则按照每一层进行查找
					$leveltype=TypeArray::getInstance();
					$brotherid=$leveltype->getArrayTypeid($level);
					if(!$brotherid){
						//返回错误
						echo "{__LINE__}：无法获取同类多重类型id<br/>";
						return false;
					}
					foreach($brotherid as $value){
						$sql.=" OR type_id={$value} ";
					}
				}
				$sql.=");";
				if($this->debug){
					echo "sql:".$sql."<br/>";
				}
				//执行sql语句
				if(!$this->db->query($sql)){
					echo "数据库除重失败！<br/>";
					//事务回滚
					$this->db->rollback_transaction();
					return false;
				}
			}
			//插入数据库
			$sql="INSERT INTO `{$this->db->tablepre}forum_gc_excellent_thread`(`tid`,`type_id`,`rate`) VALUES ";
			foreach($type_id as $value){
				$sql.="({$tid},{$value},{$rate}),";
			}
			//sql语句去掉最后一个逗号，并加上分号
			$sql=substr($sql,0,-1);
			$sql.=";";
			if($this->debug){
				echo "INSERT：{$sql}<br/>";
			}
			//运行sql语句
			if(!$this->db->query($sql)){
				echo "插入操作失败<br/>";
				$this->db->rollback_transaction();
				return false;
			}
			//提交事务
			$this->db->commit_transaction();
			//返回
			return true;
		}
		//帖子分类函数
		//输入参数为$tid(int变量)，$type_id（数组）,$rate（string变量）数组
		//此为新的插入函数，应用于新的树结构思想
		//1、将主分类和次分类作为节点的多个不同的子节点，所以必须允许在同一棵树上的两个节点可以联系同一个tid
		//2、也因此，查重将更加简单：不在需要用过查重整棵树，只需要查重树中是否含有tid即可
		//3、因此更新函数为原型，参数格式不变
		public function insertThreadType($tid,$type_id,$rate){
			if($this->debug){
				echo "开始insertThreadTypeNew函数<br/>";
			}
			//参数检查
			if(empty($tid)||!is_array($type_id)||empty($type_id)){
				echo "参数错误<br/>";
				return false;
			}
			//检查评论项,更改其格式（为空则置NULL，为str则加上单引号）
			if($rate==NULL||!is_string($rate)){
				$rate="NULL";
			}else{
				$rate="'{$rate}'";
			}
			/*新版不用查level
			//获取type的level，构造type数组(type_id=>type_level)
			$type=$this->getType($type_id);
			if(!$type){
				echo "请检查type_id参数";
				return false;
			}
			if($this->debug){
				echo "构造type数组<br/>";
			}*/
			//检查数据库是否已经存在该分类
			$sql="";
			foreach($type_id as $key=>$value){
				$sql="SELECT count(*) as 'num' FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND type_id={$value};";
				$sqldata=$this->db->query($sql);
				$row=$sqldata->fetch_assoc();
				if($row['num']>0){
					//说明已存在该类关系，则去掉数组值
					unset($type_id[$key]);
					//unset($type[$value]);
				}
			}
			if(empty($type_id)){
				if($this->debug)
					echo "该分类关系已经存在<br/>";
				return true;
			}
			//上面函数已经检查好参数
			//先进行数据库查重
			//构造sql语句
			$sql="";
			//开始事务
			$this->db->start_transaction();
			//旧版查重
			/*旧版查重
			//数据库查重
			foreach($type as $id=>$level){
				$sql="DELETE FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND ( 0 ";//此处加空格,直接删除同类型的tid联系
				if($level<30){
					//是树节点，需要查找其所有的子节点，看是否有重复
					$tree=DataTree::getInstance();
					//获取其所有子节点id
					//参数为空则是获取所有节点
					$childid_array=$tree->getOneTreeNodeAllTypeid();
					foreach($childid_array as $value){
						$sql.=" OR type_id={$value} ";
					}

				}else{
					//是多重分类，则按照每一层进行查找
					$leveltype=TypeArray::getInstance();
					$brotherid=$leveltype->getArrayTypeid($level);
					if(!$brotherid){
						//返回错误
						echo "{__LINE__}：无法获取同类多重类型id<br/>";
						return false;
					}
					foreach($brotherid as $value){
						$sql.=" OR type_id={$value} ";
					}
				}
				$sql.=");";
				if($this->debug){
					echo "sql:".$sql."<br/>";
				}
				//执行sql语句
				if(!$this->db->query($sql)){
					echo "数据库除重失败！<br/>";
					//事务回滚
					$this->db->rollback_transaction();
					return false;
				}
			}
			*/
			//新版查重
			$sql="DELETE FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid};";
			//执行sql语句
			if(!$this->db->query($sql)){
				echo "数据库除重失败！<br/>";
				//事务回滚
				$this->db->rollback_transaction();
				return false;
			}
			//插入数据库
			$sql="INSERT INTO `{$this->db->tablepre}forum_gc_excellent_thread`(`tid`,`type_id`,`rate`) VALUES ";
			foreach($type_id as $value){
				$sql.="({$tid},{$value},{$rate}),";
			}
			//sql语句去掉最后一个逗号，并加上分号
			$sql=substr($sql,0,-1);
			$sql.=";";
			if($this->debug){
				echo "INSERT：{$sql}<br/>";
			}
			//运行sql语句
			if(!$this->db->query($sql)){
				echo "插入操作失败<br/>";
				$this->db->rollback_transaction();
				return false;
			}
			//提交事务
			$this->db->commit_transaction();
			//返回
			return true;
		}
		//按照帖子id删除所有的与帖子相关联的分类关系
		public function deleteThreadType($tid){
			if($this->debug)echo "删除帖子分类关系<br/>";
			if(!is_numeric($tid)){
				echo "deleteThreadType请输入正确的参数<br/>";
				if($this->debug)echo "删除帖子分类关系失败<br/>";
				return false;
			}
			$this->db->start_transaction();
			$sql="DELETE FROM `{$this->db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid};";
			if(!$this->db->query($sql)){
				echo "数据库操作失败，删除分类失败<br/>";
				$this->db->rollback_transaction();
				return false;
			}
			$this->db->commit_transaction();
			if($this->debug)echo "删除帖子分类关系成功<br/>";
			return true;
		}
		//静态的获取分类帖子函数
		//参数为含有type_id的数组
		public static function getThreadOldSFun($type_id){
			if(!is_array($type_id)){
				echo "type_id参数非数组形式！参数错误<br/>";
				return false;
			}
			$db=GCDB::getInstance();
			$onlyonetree=0;
			$type=array();
			//1、获取type数组
			//循环获取type_id对应的level
			foreach($type_id as $value){
				if(!is_numeric($value)){//判断value是否为数字
					echo "type_id数组值非法！参数错误<br/>";
					return false;
				}
				$sql="SELECT `type_id`,`type_level` FROM `{$db->tablepre}forum_gc_type_thread` WHERE type_id={$value};";
				$sqldata=$db->query($sql);
				$row=$sqldata->fetch_assoc();
				//数据库不存在该类型id
				if(empty($row)){
					echo "数据库不存在该type_id，请不要编造！<br/>";
					return false;
				}
				//获取level，设置$this->type数组
				$type[$row['type_id']]=$row['type_level'];
				if($row['type_level']<30){
					$onlyonetree+=1;
				}
			}
			if($onlyonetree!=1){
				echo "有且只能有一个树节点的分类！分类参数错误！<br/>";
				return false;
			}
			//2、得到type数组后，开始获取thread数组
			//1)、获取该类下所有的tid
			//此变量用于查找数据库的临时表的临时变量
			if(empty($type)){
				echo "type数组为空<br/>";
				return false;
			}
			$tmptablenum=ord('A');
			$tmptablechr=chr($tmptablenum);
			
			$totalsql="
				SELECT {$tmptablechr}.tid \n
				FROM `{$db->tablepre}forum_gc_excellent_thread` as {$tmptablechr} \n";//此处记得留空格
			//获取树节点及其子节点的分类
			//写sql语句
			foreach($type as $type_id=>$type_level){
				$sql="";
				$tmpoftmptablechr=$tmptablechr;
				$tmptablenum+=1;
				$tmptablechr=chr($tmptablenum);
				if($type_level<30){//说明是树节点
					//获取树节点即其所有子节点
					$tree=DataTree::getInstance();
					//获取type_id指定的节点
					$treetypeid=$tree->getOneTreeNodeAllTypeid($type_id);
					//获取所有节点id
					
					//写sql语句
					$sql="SELECT tid FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE 0";//此处有空格
					foreach($treetypeid as $value){
						$sql.=" OR type_id={$value}";
					}
					//没有分号
				}else{
					//多重分类，直接写
					$sql="SELECT tid FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE type_id={$type_id}";
				}
				//组装总的sql语句
				$totalsql.="INNER JOIN \n (".$sql.") as {$tmptablechr} ON {$tmpoftmptablechr}.tid={$tmptablechr}.tid \n";//留空格
			}
			$totalsql.=";";
			
		//2、得到sql语句之后，执行获得所有的tid，再根据tid获得所有的帖子实例，进行输出
			$sqldata=$db->query($totalsql);
			if(!$sqldata){
				echo "sql语句出错<br/>";
				return false;
			}
			$tid=array();
			while($row=$sqldata->fetch_assoc()){
				$tid[]=$row['tid'];
			}
			if(empty($tid)){
				echo "未找到符合分类的帖子<br/>";//即使没有帖子，也应该返回true
				return false;
			}
			$result=array();
			//获取tid指定的所有帖子
			foreach($tid as $value){
				$result[$value]=new GCThreadNode($value);
				//$this->result[$value]->showThreadNode();
			}
			return $result;
		}
		//静态的获取分类帖子函数
		//参数为含有type_id的数组
		//新的获取函数，不包含level检查
		public static function getThreadSFun($type_id){
			if(!is_array($type_id)||empty($type_id)){
				//echo "type_id参数非数组形式！参数错误<br/>";
				return array(false,"type_id参数非数组形式！参数错误");
			}
			$db=GCDB::getInstance();
			//旧版查找level语句
			/*
			$onlyonetree=0;
			$type=array();
			//1、获取type数组
			//循环获取type_id对应的level
			foreach($type_id as $value){
				if(!is_numeric($value)){//判断value是否为数字
					echo "type_id数组值非法！参数错误<br/>";
					return false;
				}
				$sql="SELECT `type_id`,`type_level` FROM `{$db->tablepre}forum_gc_type_thread` WHERE type_id={$value};";
				$sqldata=$db->query($sql);
				$row=$sqldata->fetch_assoc();
				//数据库不存在该类型id
				if(empty($row)){
					echo "数据库不存在该type_id，请不要编造！<br/>";
					return false;
				}
				//获取level，设置$this->type数组
				$type[$row['type_id']]=$row['type_level'];
				if($row['type_level']<30){
					$onlyonetree+=1;
				}
			}
			if($onlyonetree!=1){
				echo "有且只能有一个树节点的分类！分类参数错误！<br/>";
				return false;
			}
			//2、得到type数组后，开始获取thread数组
			//1)、获取该类下所有的tid
			//此变量用于查找数据库的临时表的临时变量
			if(empty($type)){
				echo "type数组为空<br/>";
				return false;
			}*/
			//查找数据库
			$tmptablenum=ord('A');
			$tmptablechr=chr($tmptablenum);

			$totalsql="
				SELECT {$tmptablechr}.tid \n
				FROM `{$db->tablepre}forum_gc_excellent_thread` as {$tmptablechr} \n";//此处记得留空格
			//获取树节点及其子节点的分类
			//写sql语句
			foreach($type_id as $id){
				$sql="";
				$tmpoftmptablechr=$tmptablechr;
				$tmptablenum+=1;
				$tmptablechr=chr($tmptablenum);
				//说明是树节点
					//获取树节点即其所有子节点
					$tree=DataTree::getInstance();
					//获取type_id指定的节点
					$treetypeid=$tree->getOneTreeNodeAllTypeid($id);
					//获取所有节点id

					//写sql语句
					$sql="SELECT tid FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE 0";//此处有空格
					foreach($treetypeid as $value){
						$sql.=" OR type_id={$value}";
					}
					//没有分号

				//组装总的sql语句
				$totalsql.="INNER JOIN \n (".$sql.") as {$tmptablechr} ON {$tmpoftmptablechr}.tid={$tmptablechr}.tid \n";//留空格
			}
			$totalsql.=";";

			//2、得到sql语句之后，执行获得所有的tid，再根据tid获得所有的帖子实例，进行输出
			$sqldata=$db->query($totalsql);
			if(!$sqldata){
				//echo "sql语句出错<br/>";
				return array(false,"sql语句出错");
			}
			$tid=array();
			while($row=$sqldata->fetch_assoc()){
				$tid[]=$row['tid'];
			}
			if(empty($tid)){
				//echo "未找到符合分类的帖子<br/>";//即使没有帖子，也应该返回true
				return array(false,"未找到符合分类的帖子");
			}
			$result=array();
			//获取tid指定的所有帖子
			foreach($tid as $value){
				$result[$value]=new GCThreadNode($value);
				//$this->result[$value]->showThreadNode();
			}
			return $result;
		}
		//获取指定的type数组（不检查type_id数组）
		public static function getTypeSFun($type_id){
			$onlyonetree=0;	
			//获取数据库操作对象
			$db=GCDB::getInstance();
			//循环获取type_id对应的level
			foreach($type_id as $value){
				if(!is_numeric($value)){//判断value是否为数字
					echo "type_id数组值非法！参数错误<br/>";
					return false;
				}
				$sql="SELECT `type_id`,`type_level` FROM `{$db->tablepre}forum_gc_type_thread` WHERE type_id={$value};";
				$sqldata=$db->query($sql);
				$row=$sqldata->fetch_assoc();
				//数据库不存在该类型id
				if(empty($row)){
					echo "数据库不存在该type_id，请不要编造！<br/>";
					return false;
				}
				//获取level，设置$this->type数组
				$type[$row['type_id']]=$row['type_level'];
				if($row['type_level']<30){
					$onlyonetree+=1;
				}
			}
			if($onlyonetree!=1){
				echo "有且只能有一个树节点的分类！分类参数错误！<br/>";
				return false;
			}
			return $type;
		}
		//静态更新帖子分类函数
		//输入参数为$tid(int变量)，$type_id（数组）,$rate（string变量）
		public static function insertThreadTypeOldSFun($tid,$type_id,$rate){
			//参数检查
			if(empty($tid)||!is_array($type_id)||empty($type_id)){
				echo "参数错误<br/>";
				return false;
			}
			//检查评论项,更改其格式（为空则置NULL，为str则加上单引号）
			if($rate==NULL||!is_string($rate)){
				$rate="NULL";
			}else{
				$rate="'{$rate}'";
			}
			//获取type的level，构造type数组(type_id=>type_level)
			$type=ThreadWithType::getTypeSFun($type_id);
			if(!$type){
				echo "请检查type_id参数";
				return false;
			}
			//获取其数据库操作对象
			$db=GCDB::getInstance();
			//检查数据库是否已经存在该分类
			$sql="";
			$countnum=0;
			foreach($type_id as $key=>$value){
				$sql="SELECT count(*) as 'num' FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND type_id={$value};";
				$sqldata=$db->query($sql);
				$row=$sqldata->fetch_assoc();
				if($row['num']>0){
					//说明已存在该类关系，则去掉数组值
					//unset($type_id[$key]);
					//unset($type[$value]);
					$countnum+=1;
				}
			}
			if(count($type)==$countnum){
				echo "{__LINE__}:该分类关系已经存在<br/>";
				return true;
			}
			//上面函数已经检查好参数
			//先进行数据库查重
			//构造sql语句
			//开始事务
			$db->start_transaction();
			//数据库查重
			foreach($type as $id=>$level){
				$sql="DELETE FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND ( 0 ";//此处加空格,直接删除同类型的tid联系
				if($level<30){
					//是树节点，需要查找其所有的子节点，看是否有重复
					$tree=DataTree::getInstance();
					//获取其所有子节点id
					//参数为空则是获取所有节点
					$childid_array=$tree->getOneTreeNodeAllTypeid();
					foreach($childid_array as $value){
						$sql.=" OR type_id={$value} ";
					}
					
				}else{
					//是多重分类，则按照每一层进行查找
					$leveltype=TypeArray::getInstance();
					$brotherid=$leveltype->getArrayTypeid($level);
					if(!$brotherid){
						//返回错误
						echo "{__LINE__}：无法获取同类多重类型id<br/>";
						return false;
					}
					foreach($brotherid as $value){
						$sql.=" OR type_id={$value} ";
					}
				}
				$sql.=");";
					echo "{__LINE__}:sql:".$sql."<br/>";
			
				//执行sql语句
				if(!$db->query($sql)){
					echo "数据库除重失败！<br/>";
					//事务回滚
					$db->rollback_transaction();
					return false;
				}
			}
			//插入数据库
			$sql="INSERT INTO `{$db->tablepre}forum_gc_excellent_thread`(`tid`,`type_id`,`rate`) VALUES ";
			foreach($type_id as $value){
				$sql.="({$tid},{$value},{$rate}),";
			}
			//sql语句去掉最后一个逗号，并加上分号
			$sql=substr($sql,0,-1);
			$sql.=";";
			
				echo "{__LINE__}:INSERT：{$sql}<br/>";
			//运行sql语句
			if(!$db->query($sql)){
				echo "插入操作失败<br/>";
				$db->rollback_transaction();
				return false;
			}
			//提交事务
			$db->commit_transaction();
			//返回
			return true;
		}
		//静态更新帖子分类函数
		//输入参数为$tid(int变量)，$type_id（数组）,$rate（string变量）
		//此为新的插入函数，应用于新的树结构思想
		//1、将主分类和次分类作为节点的多个不同的子节点，所以必须允许在同一棵树上的两个节点可以联系同一个tid
		//2、也因此，查重将更加简单：不在需要用过查重整棵树，只需要查重树中是否含有tid即可
		//3、因此更新函数为New，参数格式不变
		public static function insertThreadTypeSFun($tid,$type_id,$rate){
			//参数检查
			if(empty($tid)||!is_array($type_id)||empty($type_id)){
				//echo "参数错误<br/>";
				return array(false,"参数错误");
			}
			//检查评论项,更改其格式（为空则置NULL，为str则加上单引号）
			if($rate==NULL||!is_string($rate)){
				$rate="NULL";
			}else{
				$rate="'{$rate}'";
			}
			//新版不需要获取type_level
			/*
			//获取type的level，构造type数组(type_id=>type_level)
			$type=ThreadWithType::getTypeSFun($type_id);
			if(!$type){
				echo "请检查type_id参数";
				return false;
			}
			*/
			//获取其数据库操作对象
			$db=GCDB::getInstance();
			//检查数据库是否已经存在该分类
			$sql="";
			$countnum=0;
			foreach($type_id as $key=>$value){
				$sql="SELECT count(*) as 'num' FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND type_id={$value};";
				$sqldata=$db->query($sql);
				$row=$sqldata->fetch_assoc();
				if($row['num']>0){
					//说明已存在该类关系，则去掉数组值
					//unset($type_id[$key]);
					//unset($type[$value]);
					$countnum+=1;
				}
			}
			if(count($type_id)==$countnum){
				//echo __LINE__.":该分类关系已经存在<br/>";
				return array(true,__LINE__.":该分类关系已经存在");
			}
			//上面函数已经检查好参数
			//先进行数据库查重
			//构造sql语句
			//开始事务
			$db->start_transaction();
			//旧版数据库查重
			/*
			foreach($type as $id=>$level){
				$sql="DELETE FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid} AND ( 0 ";//此处加空格,直接删除同类型的tid联系
				if($level<30){
					//是树节点，需要查找其所有的子节点，看是否有重复
					$tree=DataTree::getInstance();
					//获取其所有子节点id
					//参数为空则是获取所有节点
					$childid_array=$tree->getOneTreeNodeAllTypeid();
					foreach($childid_array as $value){
						$sql.=" OR type_id={$value} ";
					}

				}else{
					//是多重分类，则按照每一层进行查找
					$leveltype=TypeArray::getInstance();
					$brotherid=$leveltype->getArrayTypeid($level);
					if(!$brotherid){
						//返回错误
						echo "{__LINE__}：无法获取同类多重类型id<br/>";
						return false;
					}
					foreach($brotherid as $value){
						$sql.=" OR type_id={$value} ";
					}
				}
				$sql.=");";
				echo "{__LINE__}:sql:".$sql."<br/>";

				//执行sql语句
				if(!$db->query($sql)){
					echo "数据库除重失败！<br/>";
					//事务回滚
					$db->rollback_transaction();
					return false;
				}
			}*/
			//新版数据库查重
			$sql="DELETE FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid};";
			//执行sql语句
			if(!$db->query($sql)){
				//echo "数据库除重失败！<br/>";
				//事务回滚
				$db->rollback_transaction();
				return array(false,"数据库除重失败");
			}
			//插入数据库
			$sql="INSERT INTO `{$db->tablepre}forum_gc_excellent_thread`(`tid`,`type_id`,`rate`) VALUES ";
			foreach($type_id as $value){
				$sql.="({$tid},{$value},{$rate}),";
			}
			//sql语句去掉最后一个逗号，并加上分号
			$sql=substr($sql,0,-1);
			$sql.=";";

			//echo "{__LINE__}:INSERT：{$sql}<br/>";
			//运行sql语句
			if(!$db->query($sql)){
				//echo "插入操作失败<br/>";
				$db->rollback_transaction();
				return array(false,"插入操作失败");
			}
			//提交事务
			$db->commit_transaction();
			//返回
			return true;
		}
		//按照帖子id删除所有的与帖子相关联的分类关系
		public static function deleteThreadTypeSFun($tid){
			
			if(!is_numeric($tid)){
				//echo "deleteThreadType请输入正确的参数<br/>";
				return array(false,"deleteThreadType请输入正确的参数",);;
			}
			//获取数据库操作实例
			$db=GCDB::getInstance();
			$db->start_transaction();
			$sql="DELETE FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid};";
			if(!$db->query($sql)){
				//echo "数据库操作失败，删除分类失败<br/>";
				$db->rollback_transaction();
				return array(false,"数据库操作失败，删除分类失败");
			}
			$db->commit_transaction();
			return true;
		}
		//判断该tid指定的帖子是否已分类
		//返回值介绍：-1：查询过程出错
		//			  0(false)：未分类
		//            array();已存在分类,并返回分类的id数组
		public static function beCategoried($tid)
		{
			//若参数不是数字，则报错
			if(!is_numeric($tid)) {
				//echo "参数错误<br/>";
				return -1;
			}
			//查找数据库
			$db=GCDB::getInstance();
			$sql="SELECT type_id FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE tid={$tid};";
			//echo "sql:{$sql}";
			$sqldata=$db->query($sql);
			if(!$sqldata) {
				//echo "查询数据库出错<br/>";
				return -1;
			}
			$row=$sqldata->fetch_assoc();
			if(empty($row)){
				//没有分类
				return false;
			}
			//已分类
			$type_array=array();
			$type_array[]=$row['type_id'];
			while($row=$sqldata->fetch_assoc()) {
				$type_array[]=$row['type_id'];
			}
			return $type_array;
		}
	}

?>