<?php
	//http://localhost/discuz/upload/forum.php?mod=attachment&aid=MTF8MWE1ZDM1Y2V8MTQ1MjgzOTkxNHwxfDQ%3D
	//http://localhost/discuz/upload/forum.php?mod=attachment&aid=MTF8Y2JhYWY4YmF8MTQ1Mjg1MDYzNnwxfDQ%3D
	$str="MTB8MTAwOWUyYmV8MTQ1MjgzOTMxNHwxfDQ%3D";
	echo urldecode($str)."<br/>";
	echo base64_decode(urldecode($str))."<br/>";
	print_r($data_array=daddslashes(explode('|', base64_decode(urldecode($str))))); 
	echo "<br/>";
	list($_GET['aid'], $_GET['k'], $_GET['t'], $_GET['uid'], $_GET['tableid'])=$data_array;
	$requestmode = !empty($_GET['request']) && empty($_GET['uid']);
	$aid = intval($_GET['aid']);
	$k = $_GET['k'];
	$t = $_GET['t'];
	$authk = !$requestmode ? substr(md5($aid.md5($_G['config']['security']['authkey']).$t.$_GET['uid']), 0, 8) : md5($aid.md5($_G['config']['security']['authkey']).$t);
	
	echo substr(md5($aid.md5($_G['config']['security']['authkey']).$t.$_GET['uid']), 0, 8)."<br/>";
	echo $k."<br/>";
	echo $authk."<br/>";
	$aid=intval(9);
	$t=time();
	echo "t:".$t."<br/>";
	$k=substr(md5($aid.md5($_G['config']['security']['authkey']).$t.$_GET['uid']), 0, 8);
	echo "k:".$k."<br/>";
	$table_id=1;	
	$str=$aid."|".$k."|".$t."|".$_GET['uid']."|".$table_id;
	echo $str."<br/>";
	$str=urlencode(base64_encode($str));
	echo $str."<br/>";
	echo "http://localhost/discuz/upload/forum.php?mod=attachment&aid=".$str."<br/>";

	
	
?>