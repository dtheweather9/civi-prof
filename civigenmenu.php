<?php
// bpcivi_settings_page() displays the page content for the Test settings submenu


function bpcivi_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
global $wpdb;
//Import the core Civicrm Files
  include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
//What to do with the post
if (count($_POST)>1) { //count($_POST)
	//Commit Post to Variable
		$bpcivi_profilepost = $_POST;
	//Split between data fields
			$bpcivi_profilepostkeys = array_keys($bpcivi_profilepost);
			$bpcivi_profilepostvalues = array_values($bpcivi_profilepost);
	//Delete Current Table
		$bpcivi_delgrprecords = $wpdb->get_results('DELETE FROM wp_bpcivi_profilesync');
	//Insert _Post data into form
		for ($a=0; $a<count($bpcivi_profilepostvalues); $a++) {
				$wpdb->insert('wp_bpcivi_profilesync',array('ProfileName' => $bpcivi_profilepostkeys[$a],'IsUsed' => $bpcivi_profilepostvalues[$a],));
		}
}


	$bpcivi_settingprofile = $wpdb->get_results("SELECT * FROM wp_bpcivi_profilesync");	
		for ($b=0; $b<count($bpcivi_settingprofile); $b++) {
			$bpcivi_settingprofilearr[$b] = get_object_vars($bpcivi_settingprofile[$b]);
			$bpcivi_profarr[$bpcivi_settingprofilearr[$b]['ProfileName']] = $bpcivi_settingprofilearr[$b]['IsUsed'];
		}



//Show the display
echo '<div id=bpciviopts">';
echo '<div id="bpcivigroupmenu" style="min-width: 475px;float:left">';
//Need to include settings for:
	// User page
		//$bpcivi_profilepageoptionsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest',
		//  'sequential' => 1, );
		//$bpcivi_profilepageoptionsresult = civicrm_api('Contact', 'getfields', $bpcivi_profilepageoptionsparams);
	//Run through each type and identify in form
	
		//Set Defaults
			$default_profile = 1;
			$default_friends = 0;
		//Load Settings for civiprof_editprofnum
    	$profnum = get_option('civiprof_editprofnum');
    	if($profnum == false) {  //First time or if options are cleared
	    	update_option('civiprof_editprofnum',$default_profile);
	    	$profnum = $default_profile;
    	} 
    	//Load Settings for civiprof_friendsnum
    	$friendsnum = get_option('civiprof_friendsnum');
    	if($friendsnum == false) {  //First time or if options are cleared
	    	update_option('civiprof_friendsnum',$default_friends);
	    	$friendsnum = $default_friends;
    	} 
    	
    	//Post Response for profnum
    	if(isset($_POST['profnum'])) { //Organization Post Response
    		if( get_option('civiprof_editprofnum') !== $_POST['profnum']) {
    		//New Organiation
    			update_option('civiprof_editprofnum',$_POST['profnum']);
    			$profnum = get_option('civiprof_editprofnum');
    		} 
    		
    	}
    	//Post Response for friendsnum
    	if(isset($_POST['friendsnum'])) { //Organization Post Response
    		if( get_option('civiprof_friendsnum') !== $_POST['friendsnum']) {
    		//New Organiation
    			update_option('civiprof_friendsnum',$_POST['friendsnum']);
    			$friendsnum = get_option('civiprof_friendsnum');
    		} 
    		
    	}	
	//Form
	echo "<h2>";
		echo "Civicrm - Buddypress Syncronization Options";
	echo "</h2>";
	echo '<form action="" method="post">';
	echo 'Civicrm Profile ID for Member Edit: <input type="number" name="profnum" min="1" max="99999" value="' . $profnum . '"><br>';
	echo 'Relationship used for Friends: <input type="number" name="friendsnum" min="1" max="99999" value="' . $friendsnum . '"><br>';
	echo '<input type="submit" value="Submit">';
	
	echo "</form>";
echo "</div>";
echo '<div id=formview" style="float:left;">';

// CSS Files
// Enable or Disable Friendsinc

echo "</div>";
echo "</div>";
 
}

?>
