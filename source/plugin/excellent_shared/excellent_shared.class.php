<link href="../../../inc/bootstrap-3.3.5-dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<?php
/**
 *
 *  quick_reply.class.php 2016-1-1 龚成
 */
if(!defined('IN_DISCUZ')) {
　　exit('Access Denied');
}
class plugin_excellent_shared{
	
	}
class plugin_excellent_shared_forum extends plugin_excellent_shared{
	public function viewthread_postbutton_top_output(){
		$str='';
		$querydata=DB::query("SELECT * FROM ".DB::table('forum_gc_excellent_thread')." WHERE `tid`=".$_GET['tid'].";");
		$ex_field=DB::fetch($querydata);
		if(empty($ex_field)){
			//若没有数据，则添加数据
			if($_POST['insert_excellent_submit']){
				//echo "insert<p>";
				$sql = "INSERT into ".DB::table('forum_gc_excellent_thread')."(`tid`) VALUES(".$_GET['tid'].");";
        		DB::query($sql);
        		//cpmsg('加精成功!', 'forum.php?mod=viewthread&tid='.$_GET['tid'],'succeed');
				$str="<button type='submit' name='delete_excellent_submit' value='1' class='btn btn-primary'>
					<span class='glyphicon glyphicon-export'></span> 撤销加精 
				</button>";
			}else{
				$str="<button type='submit' name='insert_excellent_submit' value='1' class='btn btn-danger'>
					<span class='glyphicon glyphicon-import'></span> 加&nbsp;&nbsp;精 
				</button>";
			}
			
		}else{
			if($_POST['delete_excellent_submit']){
				//echo "delete<p>";
				$sql = "DELETE from ".DB::table('forum_gc_excellent_thread')." where `tid`=".$_GET['tid'].";";
        		DB::query($sql);
        		//cpmsg('加精成功!', 'forum.php?mod=viewthread&tid='.$_GET['tid'],'succeed');
				$str="<button type='submit' name='insert_excellent_submit' value='1' class='btn btn-danger'>
					<span class='glyphicon glyphicon-import'></span> 加&nbsp;&nbsp;精 
				</button>";
			}else{
				$str="<button type='submit' name='delete_excellent_submit' value='1' class='btn btn-primary'>
					<span class='glyphicon glyphicon-export'></span> 撤销加精 
				</button>";
			}
		}
		
		$shared_button="
			<form method='post' name=\"about_excellent\" action='forum.php?mod=viewthread&tid=".$_GET['tid']."' class=\"margin-base-vertical\">
				".$str."
			</form>
		";
		
		/*
		$shared_button="
			<select name='gf'>
				<option value='1'>1</option>
				<option value='2'>2</option>
			</select>
		";
		*/
		return $shared_button;
	}
}

?>