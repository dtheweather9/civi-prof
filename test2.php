<?php
	//Bootstrap wp
	$civiprof_baseurl = str_replace("/wp-content/plugins/civi-prof","", getcwd());
	include_once($civiprof_baseurl . '/wp-blog-header.php');
	//Do Action
	//include_once($civiprof_baseurl . '/civibp_groupshook.php');
	do_action('wp_login', 190, 190);
?>