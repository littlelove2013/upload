<?php
/**
 *
 *  quick_reply.class.php 2016-1-1 龚成
 */
if(!defined('IN_DISCUZ')) {
　　exit('Access Denied');
}
class plugin_quick_reply{
	
	}
class plugin_quick_reply_forum extends plugin_quick_reply{
  public function viewthread_fastpost_content_output(){
	  	global $_G;//全部变量
		$config = $_G['cache']['plugin']['quick_reply'];//获取插件的变量信息
		if($_G['uid'] && $config['disable'])//用户是否登陆，快复回复是否禁用
		{
			$border_color = $config['border_color'] ? $config['border_color'] : ' #C2D5E3';
			$bg_color = $config['bg_color'] ? $config['bg_color'] : '#E5EDF2';
			$left_content = $config['left_content'];
			$default_content = $config['select_default'];
			//获取下拉框中的内容，并且定义以[br]分割所填的内容。
			$select_content = explode('[br]', str_replace(array("\n\r", "\t",), array('', ''), $config['select_content']));
			$str = '<div style="border:'.$border_color.' 1px solid; background-color:'.$bg_color.'; height:24px; padding-top:2px;">&nbsp;&nbsp;'.$left_content.'&nbsp;&nbsp;<select id="quick_reply" style="height: 22px" onchange="quick_reply_z()" >
			<option value="">'.$default_content.'</option>';
			if($select_content)
			{
				foreach($select_content as $v)
				{
   					if(empty($v))continue;
      				$str .= '<option value="'.$v.'">'.$v.'</option>';
   				}
    			$str .= '</select>&nbsp;&nbsp;</div>';
     			$str .= '<script type="text/javascript">
							function quick_reply_z(){
   								var content = document.getElementById("quick_reply").value;
    							document.getElementById("fastpostmessage").value = qr_replacehtml(content);
							}
							function qr_replacehtml(content){
  								content = content.replace(/<\/?.+?>/g,""); 
    							content = content.replace(/[\r\n]/g, ""); 
     							return content; 
							}</script>';
				return $str;
			}
			
  		}
	}
}
?>