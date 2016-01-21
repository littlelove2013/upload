<?php
require '../source/class/class_core.php';
//print_r($_G);
$discuz = & discuz_core::instance();
$discuz->init();
print_r($_G['config']['db']);
$my_arr = array('one', 'two', 'three', 'four');
include template('testDiscuz/test1');
?>