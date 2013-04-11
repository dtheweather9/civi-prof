<?php
/*
Plugin Name: Buddypress Civicrm Syncs
Plugin URI: http://github.com/dtheweather9/civi-prof/
Description: A plugin to connect Civicrm and Buddypress
Version: 0.013
Author: Dan Pastuf
Author URI: http://www.danpastuf.com
License: GPL2
*/

//Add Profile Edit
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/bp-civiprof/profileedit.php');

//Add Image Edit
require_once(ABSPATH . 'wp-content/plugins/bp-civiprof/civieditphoto.php');

function bpcivi_display_prof() {
//echo "this is the content that takes place after the profile";
}

function bpcivi_selfcall() {
htmlentities($_SERVER['PHP_SELF']);
}


add_action('bp_after_profile_avatar_upload_content','bpcivi_editphoto');

//echo "<p> Profile Fields Information: </p>";
//echo "<pre>";
//print_r($bpciviapi_result['values']['contact_id']);
//echo "</pre>";
//echo "<p> End of Profile Fields Information </p>";


function bpcivi_editphoto() {
// Function will be unserted after the avatar change form
//Include statements
  include_once(getcwd()."/wp-content/plugins/civicrm/civicrm.php");
	include_once(getcwd()."/wp-content/plugins/civicrm/civicrm.settings.php");
	include_once(getcwd()."/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php");
	include_once(getcwd()."/wp-content/plugins/civicrm/civicrm/api/api.php");

//Get Contact ID
	$bpcivi_wpresult = get_current_user_id();
	$params_ciddet = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','uf_id' => $bpcivi_wpresult, 'domain_id' => 1);
	$bpciviapi_result = civicrm_api('UFMatch', 'get', $params_ciddet);
	$bprof_cid = $bpciviapi_result['values'][3]['contact_id'];

//Get Contact ID's image_URL
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id' => $bprof_cid,);
	$bpcivi_contacturlarray = civicrm_api('Contact', 'get', $params);

//If Image URL is blacnk set the image as the blank image
	if (empty($bpcivi_contacturlarray['values'][0]['image_URL'])) {
		$bpcivi_img = get_site_url()."/wp-content/plugins/buddypress/bp-core/images/mystery-man.jpg";
	} else {
		$bpcivi_img = $bpcivi_contacturlarray['values'][0]['image_URL'];
	}

// Echo the Image Url - Diagnostics
//	echo "<p> Contact Fields Information: </p>";
//	echo "<pre>";
//		print_r($bpcivi_contacturlarray['values'][0]['image_URL']);
//	echo "</pre>";
//	echo "<p> </p>";

// Form for the url inside of DIMs
	echo "<br>";
	echo "<div id='bpcivi-editphotoform'>";
		echo "<h2> Membership Photo </h2>";
			echo "<p> Insert an image for your photo ID card.  The photo here is seperate from your avatar image. </p>";
			echo "<p> Note: All images must be less than 7MB, Print Ready, and either a jpg, jpeg, gif, or png.  </p>";
		echo "<img src=" . $bpcivi_img . ' style="width:150px; margin-bottom:10px;">';
			//echo '<form action="' . curPageURL() . '" method="post" enctype="multipart/form-data">';
			echo '<form action="' . site_url() . "/wp-content/plugins/bp-civiprof/postfile.php" . '" method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="hvurls" value=' . curPageURL() . '>';
			echo '<input type="hidden" name="bprof_cid" value=' . $bprof_cid . '>';
			echo '<input type="hidden" name="bprof_url" value=' . get_site_url() . '>';
			echo '<br>';
			echo "File:" . '<input type="file" name="file" id="file">';
			echo '<input type="submit">';
		echo "</form>";
	echo "</div>";

// Posting Information of image
// Insert posting information here


//} //If file is send end

//	echo "<p> Contact Fields Information: </p>";
//	echo "<pre>";
//		print_r(get_defined_vars());
//	echo "</pre>";
//	echo "<p> </p>";

}  //End of Function 

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
	//$bpprof_baseurl = str_replace("/wp-content/plugins/bp-civiprof","", getcwd());
	//include_once($bpprof_baseurl . "/wp-content/plugins/bp-civiprof/civifriend.php");



//Add menu section for the general options
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/bp-civiprof/civigenmenu.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
add_options_page(__('Buddypress Civicrm General Settings','menu-bpcivi'), __('Bp-Civi General','menu-bpcivi'), 'manage_options', 'bpcivisettings', 'bpcivi_settings_page');


//Add menu section for the groups page:
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/bp-civiprof/civigroupmenu.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
add_options_page(__('Buddypress Civicrm Group Settings','menu-bpcivigroup'), __('Bp-Civi Groups','menu-bpcivigroup'), 'manage_options', 'bpcivigroupsettings', 'bpcivi_group_settings_page');

//Add menu item for managing memberships
add_action('bp_after_group_manage_members_admin','bpcivi_adminmembers');

function bpcivi_adminmembers() {
echo "<p> This is where the membership list of the organization can go. -DMP</p>";
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
