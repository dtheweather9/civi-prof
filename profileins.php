<?php
/*
Plugin Name: Buddypress Civicrm Syncs
Plugin URI: http://github.com/dtheweather9/civi-prof/
Description: A plugin to connect Civicrm and Buddypress
Version: 0.02
Author: Dan Pastuf
Author URI: http://www.danpastuf.com
License: GPL2
*/

//Add Profile Edit
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/civi-prof/profileedit.php');

//Add Image Edit Sub Navigation to the Profile
require_once(ABSPATH . 'wp-content/plugins/civi-prof/civieditphoto.php');

//Add Update information to chapter
require_once(ABSPATH . 'wp-content/plugins/civi-prof/civigroupupdateinfo.php');

function bpcivi_display_prof() {
//echo "this is the content that takes place after the profile";
}

function bpcivi_selfcall() {
htmlentities($_SERVER['PHP_SELF']);
}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

//Schedule Running of the civifriends program
	//$bpprof_baseurl = str_replace("/wp-content/plugins/bcivi-prof","", getcwd());
	//include_once($bpprof_baseurl . "/wp-content/plugins/civi-prof/civifriend.php");

//Add menu section for the general options
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/civi-prof/civigenmenu.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
add_options_page(__('Buddypress Civicrm General Settings','menu-bpcivi'), __('Bp-Civi General','menu-bpcivi'), 'manage_options', 'bpcivisettings', 'bpcivi_settings_page');

//Add menu section for the groups page:
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/civi-prof/civigroupmenu.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
add_options_page(__('Buddypress Civicrm Group Settings','menu-bpcivigroup'), __('Bp-Civi Groups','menu-bpcivigroup'), 'manage_options', 'bpcivigroupsettings', 'bpcivi_group_settings_page');

//Add menu item for managing memberships
add_action('bp_after_group_manage_members_admin','bpcivi_adminmembers');

function bpcivi_adminmembers() {
//echo "<p> This is where the membership list of the organization can go.</p>";
}

//Installation and Activation Information,as well as uninstallation information
function bpcivi_install() { global $wpdb; $table_name1 = $wpdb->prefix . "bpcivi_groupsync"; $table_name2 = $wpdb->prefix . "bpcivi_profilesync";
wp_schedule_event( time(), 'hourly', 'bpcivi_updatefriends_hook' );
$sql = "CREATE TABLE $table_name1 (id mediumint(9) NOT NULL AUTO_INCREMENT,orgid mediumint(9) NOT NULL,buddypress_group mediumint(9) NOT NULL,civimembertypeid mediumint(9) NOT NULL,UNIQUE KEY id (id));";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( "bpcivi_db_version", "0.1" );
$sql2 = "CREATE TABLE $table_name2 (id mediumint(9) NOT NULL AUTO_INCREMENT,ProfileName varchar(50) NOT NULL,IsUsed mediumint(9) NOT NULL,UNIQUE KEY id (id));";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql2 );
}
register_activation_hook( __FILE__, 'bpcivi_install' );

function bpcivi_deactivation() {
	wp_clear_scheduled_hook('bpcivi_updatefriends_hook');
}

register_deactivation_hook(__FILE__, 'bpcivi_deactivation');




?>
