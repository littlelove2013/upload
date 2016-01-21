<?php

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

if($_G[uid]==0) {
        //showmessage('not_loggedin', null, 'NOPERM');
		showmessage('请选登录', '', array(), array('login' => true));
}

include DISCUZ_ROOT.'./data/cache/plugin_'.$identifier.'.php';

$say_string = 'Hello World!';


//sendpm($toid, $subject, $message, $fromid = '');

include template('helloworld/helloworld');

?>