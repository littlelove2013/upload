<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); ?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<div class="row">
<div class="col-sm-12">
<form action="<?php echo $actionURL;?>" method="post">
<table align="center" class="table table-striped">
    	<tr>
        	<td align="center">作者</td>
            <td align="center">主题</td>
            <td align="center">回复数</td>
            <td align="center">查看数</td>
            <td align="center">最后编辑</td>
            <td align="center">最后编辑时间</td>
            <td align="center">发帖日期</td>
            <td align="center">附件状态</td>
        </tr>
        <?php $dropdownMenu="dropdownMenu";$num=1;?>    <?php if(is_array($threadwithtype)) foreach($threadwithtype as $key => $value) { ?>        	<tr>
            <td><a class="btn btn-block" href="<?php echo $gc_current_URL;?>home.php?mod=space&username=<?php echo $value->author;?>"><?php echo $value->author;?></a></td>
<td><a class="btn btn-block" href="<?php echo $gc_current_URL;?>forum.php?mod=viewthread&tid=<?php echo $value->tid;?>"> <?php echo $value->subject;?></a></td>
            <td><?php echo $value->replies;?></td>
            <td><?php echo $value->views;?></td>
            <td><a class="btn btn-block" href="<?php echo $gc_current_URL;?>home.php?mod=space&username=<?php echo $value->lastposter;?>"><?php echo $value->lastposter;?></a></td>
<td><?php echo date("Y-m-d",$value->lastpost);?></td>
            <td><?php echo date("Y-m-d",$value->dateline);?></td>
            
        	<td>
            <?php if($value->isattach) { ?>
                <div class="dropdown">
                    <?php $Menu=$dropdownMenu.$num; $num+=1;?>                    <button class="btn btn-primary dropdown-toggle" type="button" id="<?php echo $Menu;?>" data-toggle="dropdown">
                        共有<?php echo count($value->attachment);?>个附件
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="<?php echo $Menu;?>">
                        <li role="presentation" class="active">
                            <?php showAttachmentDownload($value);?>                        </li>
                    </ul>
                </div>
                <?php } else { ?>
            	<span class="text-danger">无附件</span>
            <?php } ?>
            </td>
            </tr>
        <?php } ?>
    </table>
</form>
</div>
</div>

<script src="http://libs.baidu.com/jquery/1.9.0/jquery.js" type="text/javascript"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script> 