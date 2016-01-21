<?php
include('DB.php');
$test=new Mode();
class Mode{
    //数据表名
    protected $tablename = '';
    //设置数组
    protected $options = array();
    //表域结构
    protected $info = array();
    //表的例名称
    protected $colsName = array();
    //Db类操作实例
    protected $db = null;
    //测试debug
    protected $debug = false;
    //要插入的数据
    protected $data = array();
    
    public function __construct($tablename){
        $this->debug = true;
    	$this->tablename = $tablename;
        $this->options['table'] = $tablename;
        if($this->debug)
            echo __LINE__.' :__construct():'.$this->tablename."<p>";
        $this->init();
    }
	//静态方法，直接执行sql语句
	public static function execute_sql($sql){
		if($sql==''){
			return false;
		}
		if(is_null($mydb)){
			//获取数据库句柄实例
        	$mydb = Db::getInstance();
        }
		$mydb->execute_sql($sql);
	}
    //初始化db数据库句柄
    //获取选定表的域
    public function init(){
        //获取Db类的实例
        if(is_null($this->db)){
        	$this->db = Db::getInstance();
        }
		$this->getTableFields();
		/*
		//获取表的域名
        $this->info = $this->db->getFields($this->tablename);
        foreach($this->info as $keys=>$values){
            //自增域名除外
            if(!$values['autoinc'])
        		$this->colsName[] = $keys;
            if($this->debug){
				echo $keys.":<br>";
                foreach($values as $key=>$value){
                    echo $key." : ".$value."   ";
                }
                echo "<br>";
				
            }
        }
        if($this->debug){
            echo __LINE__." :";
			print_r($this->colsName);
            echo "<p>";
        }
		*/
    }
	public function getTableFields(){
		//原先的列名置空
		$this->colsName=array();
		//获取表的域名
        $this->info = $this->db->getFields($this->tablename);
        foreach($this->info as $keys=>$values){
            //自增域名除外
            if(!$values['autoinc'])
        		$this->colsName[] = $keys;
            if($this->debug){
				echo $keys.":<br>";
                foreach($values as $key=>$value){
                    echo $key." : ".$value."   ";
                }
                echo "<br>";		
            }
        }
        if($this->debug){
            echo __LINE__." :";
			print_r($this->colsName);
            echo "<p>";
        }
	}
    public function free(){
		$this->data=array();
	}
    //建造符合当前数据表插入
    //data为要插入的数据
    public function createInsert($data){
		$this->free();
        if(empty($data))
        {
            return false;
        }
        $myData=array();
        foreach($data as $value){
			if($value==''){
				$value=NULL;
			}
        	$myData[] = $value;
        }
        for($i = 0;$i < count($this->colsName);$i++){
            //说明不能为null出为空
            if($this->info[$this->colsName[$i]][notnull] && (is_null($myData[$i])||$myData[$i]==''))
            { 
                return false; 	
            }
           	$this->data[$this->colsName[$i]] = $myData[$i];
        }
		return true;
    }
	//建造符合当前数据表插入
    //data为要更改的数据
    public function createUpdate($data){
		
		$this->free();
		//$tmp=array();
		if(!empty($data)){
			foreach($data as $key=>$value){
				//检测键名是否存在
				if(empty($this->info[$key])){
					return false;
				}
				else{
					//$this->data=$data;
				}
				//echo "进入createUpdate()<p>";
				//对为空字符串的数据置为NULL;
				if($value==''){
					$data[$key]=NULL;
				}
			}
		}
		if($this->debug){
			echo "Mode():".__LINE__.":data:<br/>";
			print_r($data);
		}
		$this->data=$data;
		return true;
	}
    //设置条件函数
    public function setOptionsWhere($where){
		$this->options['where']='';
        //是字符串
        if(is_string($where)){
            $this->options['where'] = $where;
        }
        //是数组表示的多条件
        else{
        	$tmpWhere='';
            foreach($where as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpWhere.=$value;
            }
            $this->options['where'] = $tmpWhere;
        }
    }
	public function setOptionsTable($table){
		if($table=='')
			return false;
		//先置空
		$this->options['table']='';
		 //是字符串
        if(is_string($table)){
            $this->options['table'] = $table;
        }
        //是数组表示的多条件
        else{
        	$tmpTable='';
            foreach($table as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpTable.=$value.",";
            }
            $this->options['table'] = substr($tmpTable,0,-1);
        }
		//把系统table指向最新设置的table;
		$this->tablename= $this->options['table'];
		//重新设置域名
		$this->getTableFields();
	}
	
	//设置条件函数
    public function setOptionsField($field){
		$this->options['field']='';
        //是字符串
        if(is_string($field)){
            $this->options['field'] = $field;
        }
        //是数组表示的多条件
        else{
        	$tmpField='';
            foreach($field as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpField.=$value.",";
            }
            $this->options['field'] = substr($tmpField,0,-1);;
            
        }
    }
	
	//设置分组
    public function setOptionsGroup($group){
		$this->options['group']='';
        //是字符串
        if(is_string($field)){
            $this->options['group'] = $group;
        }
        //是数组表示的多条件
        else{
        	$tmpGroup='';
            foreach($group as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpGroup.=$value.",";
            }
            $this->options['group'] = substr($tmpGroup,0,-1);; 
        }
    }
	
	//设置having
    public function setOptionsHaving($having){
		$this->options['having']='';
        //是字符串
        if(is_string($field)){
            $this->options['having'] = $having;
        }
        //是数组表示的多条件
        else{
        	$tmpHaving='';
            foreach($having as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpHaving.=$value.",";
            }
            $this->options['having'] = substr($tmpHaving,0,-1);; 
        }
    }
	
	//设置order
    public function setOptionsOrder($order){
		$this->options['order']='';
        //是字符串
        if(is_string($field)){
            $this->options['order'] = $order;
        }
        //是数组表示的多条件
        else{
        	$tmpOrder='';
            foreach($order as $value){
                if(!is_string($value)){
                	return false;
                }
            	$tmpOrder.=$value.",";
            }
            $this->options['order'] = substr($tmpOrder,0,-1);; 
        }
    }
	
	//增删查改
	public function insert($data=''){
		if($data!=''){
			if($this->debug){
				echo '<br>进入createInsert()<p>';
			}
			if(!$this->createInsert($data)){
				if($this->debug){
					echo 'createInsert()失败<p>';
					print_r($this->data);
				}
				return false;
			}
		}else{
			if(empty($this->data)){
				return false;
			}
		}
		return $this->db->insert($this->data,$this->options);
	}
	public function _insert($data,$options){
		return $this->db->insert($data,$options);
	}
	
	public function update($data){
		if($data!=''){
			if(!$this->createUpdate($data)){
				return false;
			}
		}else{
			if(empty($this->data)){
				return false;
			}
		}
		return $this->db->update($this->data,$this->options);
	}
	public function _update($data,$options){
		return $this->db->update($data,$options);
	}
	public function delete(){
		return $this->db->delete($this->options);
	}
	public function select(){
		return $this->db->select($this->options);
	}
	public function _delete($options){
		return $this->db->delete($options);
	}
	public function _select($options){
		return $this->db->select($options);
	}
	
	//获取参数函数
    public function getData(){
    	return $this->data;
    }
    public function getOptions(){
    	return $this->options;
    }
    public function getTablename(){
    	return $this->tablename;
    }
    public function getInfo(){
    	return $this->info;
    }
    public function getColsname(){
    	return $this->colsName;
    }
	public function getLastInsId(){
		return $this->db->getLastInsID();
	}
    
}
//echo "<p>Hello World</p>";
?>