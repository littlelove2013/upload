<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>testTable</title>
<link href="CSS/testTableCss.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
	header("Content-Type:text/html;charset=utf8");
	date_default_timezone_set('Asia/Shanghai');//设置为本地时间
	class showPage{
		
		private $data="";
		private $link="";
		private $head="";
		private $debug =false;
		//每页显示条数
		private $pageNum=10;
		//总页数
		private $pageTotal=0;
		//当前页数
		private $pages=1;
		//数据条数
		private $dataNum=0;
		//排序方式
		public $sortkey=1;
		//page前缀
		private $prefix='';
		public function __construct($data,$link){
			$this->debug=false;
			
			$this->setData($data);
			$this->link=$link;
		}
		public function setPagePrefix($prefix=''){
			$this->prefix=$prefix;
		}
		public function setData($data){
			$isData=false;
			if(is_array($data))
			{
				foreach($data as $keys=>$values){
					if(is_array($values)){
						$isData=true;
						break;
					}
				}
			}
			if($isData){
				/*
				if($this->sortkey%2 == 1){
					ksort($data);
				}else{
					krsort($data);
				}*/
				$this->data = $data;
			}else{
				$this->data = array();
			}
			//获取数组头
			$this->getHead();
			//获取数组数量
			$this->getDataNum();
			//设置页面数量
			$this->setPages();
		}
		//获取数组的头
		public function getHead(){
			if(empty($this->data)){
				if($this->debug){
					echo __LINE__.":数据错误<p>";
				}
				return ;
			}
			$tmp=current($this->data);
			//print_r($tmp);
			$this->head=array_keys($tmp);
			//print_r($this->head);
		}
		//获取数据数量
		public function getDataNum(){
			if(empty($this->data)){
				if($this->debug){
					echo __LINE__.":数据错误<p>";
				}
				return;
			}
			$this->dataNum=count($this->data);
		}
		
		//设置排序顺序
		public function setSortKey($sortkey)
		{
			$this->sortkey = $sortkey;
			if($this->debug){
				echo $this->sortkey."<br/>";
			}
			if($this->sortkey%2 == 1){
					ksort($this->data);
				}else{
					krsort($this->data);
			}
		}
		//设置每页显示数据量
		public function setPageNum($pageNum){
			if($pageNum>0){
				$this->pageNum=$pageNum;
				$this->setPages();
			}
		}
		//得到总的页数
		public function setPages(){
			//echo "进入SetPages<p>";
			$this->pageTotal=ceil(($this->dataNum) / ($this->pageNum));
			if($this->debug){
				echo __LINE__.":".$this->pageTotal."<br/>";
			}
		}
		
		//输出头部数据
		public function showHead(){
			foreach($this->head as $value){
				echo "<th> ".$value."</th>";
			}
		}
		
		public function setHead($arr){
			//
			if(!is_array($arr)|| count($arr)!=count($this->head)){
				echo __LINE__.":setting wrong!<p>";
			}
			$this->head=$arr;
		}
		public function getDataHead(){
			return $this->head;
		}
		public function getsd(){
		}
		//输出单条数据
		public function showData($values){
			foreach($values as $value){
				echo "<td>".$value."</td>";
			}
		}
		//输出一页的数据
		//$pages表示第几页
		public function showPages($pages,$tableName=''){
			if(!isset($pages)||$pages == ''||$pages==0||$pages>$this->pageTotal){
				$this->pages=1;
			}else{
				$this->pages = $pages;
			}
			$label=($this->pages-1)*$this->pageNum;
			//echo "label:".$label."<p>";
			$tmpKey=array_keys($this->data);
			//print_r($tmpKey);
			//print_r($this->data[$tmpKey[$label]]);
			//输出头部
			echo "<table id='showPage'>";
			if(!empty($tableName)){
				echo "<caption>
							".$tableName."
        				</caption>";
			}
			echo "<tr>";
			$this->showHead();
			echo "</tr>";
			if($this->pages < $this->pageTotal){
				//echo "没到最后一页<p>";
				for($i=$label;$i<$label+$this->pageNum;$i++){
					if($i%2==0){
						echo "<tr>";
					}else{
						echo "<tr class='alt'>";
					}
					$this->showData($this->data[$tmpKey[$i]]);
					echo "</tr>";
				}
			}else{
				//echo "最后一页<p>";
				for($i=$label;$i<$label+$this->dataNum;$i++){
					if($i%2==0){
						echo "<tr>";
					}else{
						echo "<tr class='alt'>";
					}
					$this->showData($this->data[$tmpKey[$i]]);
					echo "</tr>";
				}
			}
			//设置换页
			?>

			
            <table id="showPage" >
            	<tr align="center" valign="middle">
                	<td align="center">
                    	<?php 
							echo "共有记录".$this->dataNum."条&nbsp;每页显示".$this->pageNum."条&nbsp;第".$this->pages."页/共".$this->pageTotal."页";
							echo "&nbsp;&nbsp;";
							if($this->pages > 1){
								echo "<a href='".$this->link."?".$this->prefix."pages=1'>首&nbsp;页</a>&nbsp;";
								echo "<a href='".$this->link."?".$this->prefix."pages=".($this->pages-1)."'>上一页</a>&nbsp;";
							}
							if($this->pages< $this->pageTotal){
								echo "<a href='".$this->link."?".$this->prefix."pages=".($this->pages+1)."'>下一页</a>&nbsp;";
								echo "<a href='".$this->link."?".$this->prefix."pages=".($this->pageTotal)."'>尾&nbsp;页</a>";
							}
						 ?>
                    </td>
                </tr>
                <tr align="center" valign="middle">
                	<td align="center"> 
                    	<?php
							echo "<form name='chosePageForm' action='' method='post'>
									<select size=1 name='selectPagenum' >";
									for($i = 0;$i<$this->pageTotal;$i++){
										if($i == $this->pages-1){
											echo "<option value=".($i+1)." selected>
											第".($i+1)."页
										</option>";
										}else{
										echo "<option value=".($i+1).">
											第".($i+1)."页
										</option>";
										}
									}
							  echo "</select>&nbsp;
									<input type='button' name='chosePage' value='确认' onClick='return goNum()'/>
									<input type='hidden' id='thisLink' value= ".$this->link." />
									<input type='hidden' id='thisPrefix' value= ".$this->prefix." />
									&nbsp;
									</form>";
						?>
                    </td>
                </tr>
            </table>
			<?php
		}
		public function Display($tableName=''){
			$pages = $_GET[$this->prefix."pages"];
			$this->showPages($pages,$tableName);
		}
	}

	/*
	$testData = array(array("head"=>32,"sdf"=>42),array("head"=>4,"sdf"=>342),array("head"=>4,"sdf"=>342),array("head"=>32,"sdf"=>42),array("head"=>4,"sdf"=>342),array("head"=>4,"sdf"=>342),array("head"=>32,"sdf"=>42),array("head"=>4,"sdf"=>342),array("head"=>4,"sdf"=>342));	
	//print_r(array_keys($testData));
	$test=new showPage($testData);
	//$test->showHead();
	//$test->showData();
	//echo count($testData).'<br/>';
	//$test->setSortKey(2);
	//$test->showData();
	$test->setPageNum(5);
	//$test->showPages(2);
	$test->Display();
	//echo ceil(3/2)."<p>";
	*/

?>
</body>
<script language="javascript">
	function goNum(){
		var num = chosePageForm.selectPagenum.value;
		var myLink= document.getElementById('thisLink').value;
		var myPrefix=document.getElementById('thisPrefix').value;
		location.href = myLink+"?"+myPrefix+"pages="+num;
	}
</script>
</html>
