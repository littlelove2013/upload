<?php
    if(empty($_GET['threadid'])){
        echo "参数错误！<br/>";
    }else {
    //根据获取的threadid获取附件
        require_once ("class/includefile.php");
        require_once ("class/ThreadWithType.class.php");
        require_once ("function/function_showAttachmentDownload.php");
        $threadid=$_GET['threadid'];
        $mythread=new GCThreadNode($threadid);
        showAttachmentDownload($mythread);
    }
?>