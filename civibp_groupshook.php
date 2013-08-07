<?php
function civibp_groupshook($user_login, $user) {
    $current_user = wp_get_current_user();
    $bpcivi_currentuserid = $current_user->ID;
    //$bpcivi_currentuserid = 190;
    //Bootstrap Civicrm and wordpress
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
		$config = CRM_Core_Config::singleton();
		global $wpdb;
    //Get Current CID
    	$bpcivi_groupmemberparams = array(  'version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
    		'uf_id' => $bpcivi_currentuserid,);
		$bpcivi_groupmemberresult = civicrm_api('UFMatch', 'get', $bpcivi_groupmemberparams);
		$bpcivi_groupmemberCID = $bpcivi_groupmemberresult["values"][0]["contact_id"];
	//Find Current Memberships
		$bpcivi_membershipsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $bpcivi_groupmemberCID,);
		$bpcivi_membershipsresult = civicrm_api('Membership', 'get', $bpcivi_membershipsparams);
	//Load Group Member Table
		$bpcivisync_settinggroups = $wpdb->get_results("SELECT * FROM wp_bpcivi_groupsync");			
	//For loop to read each item from db to get what needs to be matched
		for ($i=0;$i<count($bpcivisync_settinggroups);$i++) {
			$bpcivisync_settinggroupsarr[$i] = get_object_vars($bpcivisync_settinggroups[$i]);
			$chapterarr[$bpcivisync_settinggroupsarr[$i]['civimembertypeid']] = $bpcivisync_settinggroupsarr[$i]['buddypress_group'];
			$chaptergroups[$i] = $bpcivisync_settinggroupsarr[$i]['buddypress_group'];
			$bpgroupstomembertype[$bpcivisync_settinggroupsarr[$i]['buddypress_group']] = $bpcivisync_settinggroupsarr[$i]['civimembertypeid'];
		}
		sort($bpgroupstomembertype);
	//Loop Through each civicrm user membership and join group
		for ($i=0;$i<count($bpcivi_membershipsresult['values']);$i++) {
			if ($bpcivi_membershipsresult['values'][$i]['status_id'] == 1 || $bpcivi_membershipsresult['values'][$i]['status_id'] == 2 || $bpcivi_membershipsresult['values'][$i]['status_id'] == 3) {
			//Membership current, add to groups
			groups_join_group( $chapterarr[$bpcivi_membershipsresult['values'][$i]['membership_type_id']], $bpcivi_currentuserid );
			} else {
			//Membership expired - remove from groups
			//Need Exception for group admins
			$isadmintest = groups_is_user_admin($bpcivi_currentuserid,$chapterarr[$bpcivi_membershipsresult['values'][$i]['membership_type_id']]);
			$ismodtest = groups_is_user_mod($bpcivi_currentuserid,$chapterarr[$bpcivi_membershipsresult['values'][$i]['membership_type_id']]);
			if (($isadmintest + $ismodtest) == 0) { //Test if Moderator or Admin
			groups_leave_group( $chapterarr[$bpcivi_membershipsresult['values'][$i]['membership_type_id']], $bpcivi_currentuserid );
			} //Leave admins and moderators
			}
		}
	//Loop through each buddypress group registration and remove from improper
		
		
		$bpcivi_currentusergroups = groups_get_user_groups($bpcivi_currentuserid);
		for($i=0;$i<count($bpcivi_currentusergroups['groups']);$i++) {
			if(in_array($bpcivi_currentusergroups['groups'][$i],$chapterarr)) {
				$checkgroups[$i] = $bpcivi_currentusergroups['groups'][$i];
			} 
		}
		$checkgroups = array_values($checkgroups);
		
		for($i=0;$i<count($checkgroups);$i++) {
			//Use bpgroupstomembertype and checkgroups to check if membership if valid
			//Check if record/membership exhists
			$bpcivi_validmembertype = $bpgroupstomembertype[$checkgroups[$i]];
			$bpcivi_checkmembershipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'contact_id' => $bpcivi_groupmemberCID,
				'membership_type_id' => $bpcivi_validmembertype,);
			$bpcivi_checkmembershipresult = civicrm_api('Membership', 'get', $bpcivi_checkmembershipparams);
			if ($bpcivi_checkmembershipresult['count'] > 0) {
				//Membership is valid, do nothing
			} else {
				//Membership is not valid
				//Check if admin or mod
				if(groups_is_user_admin( $bpcivi_currentuserid, $checkgroups[$i] ) || groups_is_user_mod( $bpcivi_currentuserid, $checkgroups[$i] )) {
					
				} else {
					//Not a member of the group and not an admin
					groups_leave_group( $checkgroups[$i], $bpcivi_currentuserid );
				}
			}
		}
		
    //Diagnostics
    /*
    echo "bpcivi_currentuserid: " . $bpcivi_currentuserid . "<br>";
    echo "checkgroups <pre>";
   	print_r($checkgroups);
   	echo "</pre>";
    echo "bpcivi_checkmembershipresult <pre>";
   	print_r($bpcivi_checkmembershipresult);
   	echo "</pre>";
   	echo "bpgroupstomembertype <pre>";
   	print_r($bpgroupstomembertype);
   	echo "</pre>";
   	*/
   	//echo $civibp_groupshookteststring = $bpcivi_groupmemberCID;
	//mail("dmpastuf@seds.org","Group Modification",$civibp_groupshookteststring);
}
add_action('wp_login', 'civibp_groupshook', 10, 2);