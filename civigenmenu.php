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
	$bpcivi_profilepageoptionsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest',
	  'sequential' => 1, );
	$bpcivi_profilepageoptionsresult = civicrm_api('Contact', 'getfields', $bpcivi_profilepageoptionsparams);
	//Run through each type and identify in form
	echo '<form action="" method="post">' . 'Example ID: <input type="text" name="Exnumb" value="2">' . '<table border="1">';
	echo '<tr><td>' . "Form<br>Number" . '</td><td>' . "Is<br>Custom?" . '</td><td>' . "Form Name" . '</td><td>' . "Use Field?" . '</td></tr>';
	for ($i=0;$i<count($bpcivi_profilepageoptionsresult['values']);$i++) {
	echo '<tr><td>';
		echo $i . '</td><td>';
		if (empty($bpcivi_profilepageoptionsresult['values'][$i]['title'])) {
			echo ' Yes </td><td>';			
			$displayformitem = $bpcivi_profilepageoptionsresult['values'][$i]['label'];
			$displayformitemarr[$i] = str_replace(" ","_",$displayformitem);
			echo $displayformitem;
		} else {
			echo '</td><td>';
			$displayformitem =  $bpcivi_profilepageoptionsresult['values'][$i]['title'];
			$displayformitemarr[$i] = str_replace(" ","_",$displayformitem);
			echo $displayformitem;
		}
	echo '</td><td>';
		if ($bpcivi_profarr[$displayformitemarr[$i]] == 1) {		
			echo '<input type="checkbox" name="' . $displayformitem . '" value="1" checked></td></tr>';
		} else {
			echo '<input type="checkbox" name="' . $displayformitem . '" value="1" ></td></tr>';
		}
	//Hidden Value
		//echo '<input type="hidden" value="' . $displayformitem . '" name="' . $i . '" ></td></tr>';
	}
	echo "</table>";
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
