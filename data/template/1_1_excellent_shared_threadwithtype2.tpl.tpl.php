<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); ?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css"><?php $gc_current_URL=curPageURL();
        //获取最后一个/之前得所有字符串
        $gc_current_URL=strchr($gc_current_URL,"/",true);
        $bg_path=$gc_current_URL."template/default/excellent_shared/image/bg.jpg";?><style>

    /* http://css-tricks.com/perfect-full-page-background-image/ */
    div.fixed_bg {
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
        color:#E3E3E3;
    }
    div.body {
        background-color: rgba(255, 255, 255, 0.7);
    }
    div.mediaMargin{
        margin-top:20px;
    }
    body {
        padding-top: 20px;
        font-size: 16px;
        font-family: "Open Sans",serif;
        background: transparent;
    }

    div.tabcontentbg{
        background: url(<?php echo $bg_path;?>) no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }

    div.text-standup{
        font:bold 20px/2 "微软雅黑",Arial;
        color:#FF7E00;
        text-shadow:black 2px 2px 2px;
    }
    .panel {
        background-color: rgba(255, 255, 255, 0.1);
        border:1px solid ddd;
        -webkit-box-shadow:0 0 10px 3px black;
        -moz-box-shadow:0 0 10px 3px black;
    }

    h1 {
        font-family: "Abel", Arial, sans-serif;
        font-weight: 400;
        font-size: 40px;
    }

    /* Override B3 .panel adding a subtly transparent background */
    .panel {
        background-color: rgba(255, 255, 255, 0.8);
    }

    .margin-base-vertical {
        margin: 40px 0;
    }

</style>

<body>

<div class="tabcontentbg">
<div class="row">
    <?php $dropdownMenu="dropdownMenu";$num=1;?>    <?php if(is_array($threadwithtype)) foreach($threadwithtype as $key => $value) { ?>    <div class="col-md-4 col-sm-5 mediaMargin">
        <div class="panel panel-default">
            <a class="" href="#">
                <?php $gc_image_type=getAttachType(current($value->attachment)->attachtype);
                $imagepath=$gc_current_URL."template/default/excellent_shared/image/".$gc_image_type;?>                <img class="mg-rounded" width="100%" src="<?php echo $imagepath;?>" alt="...">
            </a>
            <div class="panel-footer ">
                <div class="">
                    <a class="btn btn-primary btn-sm" href="<?php echo $gc_current_URL;?>forum.php?mod=viewthread&tid=<?php echo $value->tid;?>"> <?php echo $value->subject;?></a>
                    <small class="text-right"><?php echo date("Y-m-d",$value->dateline);?></small></div>
                <p class="small">
                <div class="dropup">
                <a class="btn btn-primary btn-sm" href="<?php echo $gc_current_URL;?>home.php?mod=space&username=<?php echo $value->author;?>"><?php echo $value->author;?></a>
                    <div class="pull-right">

                        <?php if($value->isattach) { ?>
                        <div class="dropdown">
                            <?php $Menu=$dropdownMenu.$num; $num+=1;?>                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="<?php echo $Menu;?>" data-toggle="dropdown">
                                共有<?php echo count($value->attachment);?>个附件
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="<?php echo $Menu;?>">
                                <li role="presentation" class="active">
                                    <?php showAttachmentDownload($value);?>                                </li>
                            </ul>
                        </div>
                        <?php } else { ?>
                        <span class="text-danger">无附件</span>
                        <?php } ?>

                    </div>
                </div>

                </p>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
</div>
</body>
<script src="http://libs.baidu.com/jquery/1.9.0/jquery.js" type="text/javascript"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script> 