<?php
// bpcivi_group_settings_page() displays the page content for the Test settings submenu

function bpcivi_group_settings_page() {
global $wpdb;
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

//Import the core Civicrm Files
  include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
// Perform _Post loop
if (count($_POST)>1) {
	//Commit Post to Variable
	$bpcivi_grouppost = $_POST;
	//Split between data fields
		$bpcivi_grouppostkeys = array_keys($bpcivi_grouppost);
		$bpcivi_grouppostvalues = array_values($bpcivi_grouppost);
	for ($b=0; $b<count($bpcivi_grouppostvalues); $b++) {
		$bpcivi_gmembexplode = explode("_",$bpcivi_grouppostkeys[$b]);
		$bpcivi_gmembercivigroup[$b] = $bpcivi_gmembexplode[0];
		$bpcivi_gmembermembertype[$b] = $bpcivi_gmembexplode[1];
	}
	//Delete Current Table
	$bpcivi_delgrprecords = $wpdb->get_results('DELETE FROM wp_bpcivi_groupsync');
	//Reset Auto Increment

	//Insert _Post data into form
		for ($a=0; $a<count($bpcivi_grouppostvalues); $a++) {
			$wpdb->insert('wp_bpcivi_groupsync',array('orgid' => $bpcivi_gmembercivigroup[$a],'buddypress_group' => $bpcivi_grouppostvalues[$a],'civimembertypeid' => $bpcivi_gmembermembertype[$a],));
		}
}
//Show the display, declare div
	echo "<div id=groupmenu>";
//Get List of Memberships
	$bpcivi_membershipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'options'  => array( 'limit'  => 100, 'sort'  => 'name' ),
			'is_active' => 1,);
	$bpcivi_membershiptyperesult = civicrm_api('MembershipType', 'get', $bpcivi_membershipparams);
	$imax = count($bpcivi_membershiptyperesult['values']);
	for ($i=0; $i < $imax; $i++) {
		$bpcivi_membershipname[$i] = $bpcivi_membershiptyperesult['values'][$i]['name'];
		$bpcivi_membershipdescription[$i] = $bpcivi_membershiptyperesult['values'][$i]['description'];
		$bpcivi_membershiporgid[$i] = $bpcivi_membershiptyperesult['values'][$i]['member_of_contact_id'];
		$bpcivi_membershipobjid[$i] = $bpcivi_membershiptyperesult['values'][$i]['id'];
		}
//Get List of Groups
	$bpcivi_groups = $wpdb->get_results("SELECT * FROM wp_bp_groups");
	$bpcivi_settinggroups = $wpdb->get_results("SELECT * FROM wp_bpcivi_groupsync");			

  $jmax = count($bpcivi_groups);
	for ($j=0; $j < $jmax; $j++) {
		$bpcivi_groupsarr[$j] = get_object_vars($bpcivi_groups[$j]);
		$bpcivi_groups_bpname[$j] = $bpcivi_groupsarr[$j]['name'];
		$bpcivi_groups_bpid[$j] = $bpcivi_groupsarr[$j]['id'];
	}
  $kmax = count($bpcivi_settinggroups);
	for ($k=0; $k < $kmax; $k++) {
		$bpcivi_setgroupsarr[$k] = get_object_vars($bpcivi_settinggroups[$k]);
		$bpcivi_civisetgroup[$k] = $bpcivi_setgroupsarr[$k]['orgid'];
		$bpcivi_bpsetgroup[$k] = $bpcivi_setgroupsarr[$k]['buddypress_group'];
	}

$bpcivi_groups_sel = array_combine($bpcivi_groups_bpid,$bpcivi_groups_bpname);
$bpcivi_groupmatch_sel = array_combine($bpcivi_civisetgroup,$bpcivi_bpsetgroup);

//Display Form
	echo "<p>This will match the current membership types to the buddypress groups.  Each user will be synced on an hourly basis between the designated group and their membership type.</p>";
	echo '<form action="" method="post">';
	echo '<table border="1">';
	echo '<tr><td><center><h2>Membership Type</h2></center></td><td><center><h2>Buddypress Group</h2></center></td></tr>';
	for ($i=0; $i < $imax; $i++) {
		$bpcivi_gselectid = $bpcivi_groupmatch_sel[$bpcivi_membershiporgid[$i]];
		echo '<tr><td>';
		echo $bpcivi_membershipname[$i] /* . " " . $bpcivi_membershiporgid[$i] . " " . $bpcivi_gselectid */ . " </td><td>";
		echo '<select name=' . $bpcivi_membershiporgid[$i] . '_' . $bpcivi_membershipobjid[$i] . '>';
		echo '<option value="0">--None--</option>';
			for ($j=0; $j < $jmax; $j++) {
			if ($j+1 == $bpcivi_gselectid) {
				echo '<option value="' . $bpcivi_groups_bpid[$j] . '" selected >' .  $bpcivi_groups_bpname[$j] . "</option>";
			} else {
				echo '<option value="' . $bpcivi_groups_bpid[$j] . '" >' .  $bpcivi_groups_bpname[$j] . "</option>";
				}
		}
	echo "</select></td>";
}
echo '</table>';
echo '<input type="submit">';
echo '</form>';
//Diagnostic Display
echo "</div>";

}



?>
