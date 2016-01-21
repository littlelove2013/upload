<?php
if(submitcheck('hw_submit')){
		$sql="UPDATE ".DB::table('plugin_helloworld')." SET say_string='".$_POST['textfield']."';";
        DB::query($sql);
        cpmsg('设置成功!', 'admin.php?action=plugins&identifier='.$identifier.'&mod='.$mod,'succeed');
}

$query= DB::query("SELECT * FROM ".DB::table('plugin_helloworld').";");

$hw_field=DB::fetch($query);

//print_r($hw_field);

$hw_set_saystring=$hw_field['say_string'];

$hw_formhash=FORMHASH;

include template('helloworld/setting');
?>