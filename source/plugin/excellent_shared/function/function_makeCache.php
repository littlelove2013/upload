<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

function build_cache_typetree() {
	global $root;
	global $data_array;
    $data = array();
    $data['root'] = $root;
    $data['data_array'] = $data_array;
    save_syscache('typetree', $data);
}
?>