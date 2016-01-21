<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); ?>
<div class="row">
<div class="col-sm-12">
<form action="<?php echo $actionURL;?>" method="post">
<table align="center" class="table table-striped">
    	<tr>
        	<td>类型</td>
            <td>名称</td>
            <td>大小</td>
            <td>日期</td>
            
            <td>下载</td>
        </tr>
        
    <?php if(is_array($ThreadWithAttach->attachment)) foreach($ThreadWithAttach->attachment as $key => $value) { ?>        	<tr>
            <td><?php echo $value->attachtype;?></td>
<td><?php echo $value->filename;?></td>
<td><?php echo ceil($value->gc_filesize/1024)."KB";?></td>
            <td><?php echo date("Y-m-d",$value->dateline);?></td>

        	<td><a class="btn btn-primary" href="<?php echo $value->downloadURL;?>">下载</a></td>

            </tr>
        <?php } ?>
    </table>
</form>
</div>
</div>
