<?php
	add_action('bpcivi_updatefriends_hook','bpcivi_updategroups()');
	function bpcivi_updategroups() {
//Include Files
	$civiprof_baseurl = str_replace("/wp-content/plugins/civi-prof","", getcwd());
	include_once($civiprof_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
//Get saved member types information
	$bpcivisync_settinggroups = $wpdb->get_results("SELECT * FROM wp_bpcivi_groupsync");			
//For loop to read each item from db to get what needs to be matched
	$imax = count($bpcivisync_settinggroups);
	for ($i=0;$i<$imax;$i++) {
		$bpcivisync_settinggroupsarr[$i] = get_object_vars($bpcivisync_settinggroups[$i]);
		$bpcivisync_membtype[$i] = $bpcivisync_settinggroupsarr[$i]['civimembertypeid'];
		$bpcivisync_bpgroup[$i] = $bpcivisync_settinggroupsarr[$i]['buddypress_group'];
		$bpcivisync_orgid[$i] = $bpcivisync_settinggroupsarr[$i]['orgid'];
	//Each membership type is entered and the members which need to be updated
		$bpcivisync_membertypeparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		 'membership_type_id' => $bpcivisync_membtype[$i],);
		$bpcivisync_membertyperesult[$i] = civicrm_api('Membership', 'get', $bpcivisync_membertypeparams);
		
	//Loop Through each membership and determine contactids in group
		if (count($bpcivisync_membertyperesult[$i]['values'])>0) {
			for ($j=0;$j<count($bpcivisync_membertyperesult[$i]['values']);$j++) {
					$bpcivi_groupcivimemberls[$i][$j] = $bpcivisync_membertyperesult[$i]['values'][$j]['contact_id'];
					//Loopup the users wordpress user id from their civicrm id
					$bpcivi_forgroupsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					  'contact_id' => $bpcivi_groupcivimemberls[$i][$j], );
					$bpcivi_forgroupsresult = civicrm_api('UFMatch', 'get', $bpcivi_forgroupsparams);
					$bpcivi_groupwpid[$bpcivisync_bpgroup[$i]][$j] = $bpcivi_forgroupsresult['values'][0]['uf_id'];
					$bpcivi_groupcivimemberstatus[$bpcivisync_bpgroup[$i]][$bpcivi_groupwpid[$bpcivisync_bpgroup[$i]][$j]] = $bpcivisync_membertyperesult[$i]['values'][$j]['status_id'];
				}
			}
	}//Closes wpinfo gathering loop
$i=0;
//read group membership
	$bpcivisync_bpgroupmembers = $wpdb->get_results("SELECT * FROM wp_bp_groups_members");
	for ($j=0;$j<count($bpcivisync_bpgroupmembers);$j++) {
		$bpcivisync_bpgroupmembersarr[$j] = get_object_vars($bpcivisync_bpgroupmembers[$j]);
		$bpcivisync_bpgroupid[$j] = $bpcivisync_bpgroupmembersarr[$j]['group_id'];
		$bpcivisync_bpuserid[$j] = $bpcivisync_bpgroupmembersarr[$j]['user_id'];
		$bpcivisync_bpisconfirmed[$j] = $bpcivisync_bpgroupmembersarr[$j]['is_confirmed'];
		$bpcivisync_bpisadmin[$j] = $bpcivisync_bpgroupmembersarr[$j]['is_admin'];
		$bpcivisync_bpismod[$j] = $bpcivisync_bpgroupmembersarr[$j]['is_mod'];
	}
//Flip group id - Buddypress Group Data
	for ($b=0;$b<count($bpcivisync_bpgroupmembers);$b++) {
		$bpcivi_groupmatcharray[$bpcivisync_bpgroupmembersarr[$b]['group_id']][$b] = $bpcivisync_bpgroupmembersarr[$b]['user_id'];
		sort($bpcivi_groupmatcharray[$bpcivisync_bpgroupmembersarr[$b]['group_id']]);
	}
//Loop Through what memberships need to be created
	for ($k=0;$k<count($bpcivisync_bpgroup);$k++) {  //Cycle through Groups
		for ($a=0;$a<count($bpcivi_groupcivimemberstatus[$k]);$a++) { //Cycle through members in above group
			$bpcivi_groupwpid[$k][$a]; //Wordpress id in loop - valid civicrm contact ids in wordpress form, not verified for valid membership
			$bpcivisync_bpgroup[$k]; //Buddypress Group ID
			if ($bpcivi_groupcivimemberstatus[$k][$bpcivi_groupwpid[$k][$a]] == 1 ) { 	//if Civicrm Membership is valid
				//If Buddypress Group Record Exhists
				if (in_array($bpcivi_groupwpid[$k][$a], $bpcivi_groupmatcharray[$k])) {
					//Do Nothing
				//If Buddypress Group Record does not Exhist
					} else {
					//Create Group Record
					//echo "New Record in Buddypress for " . $bpcivi_groupwpid[$k][$a] . " in " . $k . "<br>";
					$bpcivisync_newmemberwpdb = $wpdb->insert('wp_bp_groups_members', array(
						 'group_id' => $k, 'user_id' => $bpcivi_groupwpid[$k][$a],
						'inviter_id' => 0, 'is_admin' => 0, 'is_mod' => 0, 'is_confirmed' => 1,),
						array('%d','%d','%d','%d','%d','%d'));
					}
			}
		}
	}
//Loop though what memberships list to remove active memberships and if theres a re-activated one, do so
	for ($y=0;$y<count($bpcivisync_bpgroupmembersarr);$y++) { //For each membership from buddypress
		if ($bpcivisync_bpgroupmembersarr[$y]['is_admin'] == 1) {
		//if (0 == 1) {
			//echo $bpcivisync_bpgroupmembersarr[$y]['user_id'] . " is admin<br>";
		} elseif ($bpcivisync_bpgroupmembersarr[$y]['is_mod'] == 1) {
		//} elseif (0 == 1) {
			//echo $bpcivisync_bpgroupmembersarr[$y]['user_id'] . " is mod<br>";
		} else {
		//Regular member runthrough
		//Check if the buddypress record from memberarr is valid inside of the groupcivimember status array
			if (empty($bpcivi_groupcivimemberstatus[$bpcivisync_bpgroupmembersarr[$y]['group_id']][$bpcivisync_bpgroupmembersarr[$y]['user_id']]) == 1 && $bpcivisync_bpgroupmembersarr[$y]['is_confirmed'] == 1) {
				//The record exhists in buddypress but not in civicrm, and is not an admin or moderator.  Therefore this must be removed from buddypress. Make 'is_confirmed' zero.
				//echo "Remove Record in Buddypress for " . $bpcivisync_bpgroupmembersarr[$y]['user_id'] . " For Group " . $bpcivisync_bpgroupmembersarr[$y]['group_id'] . "<br>";
					$bpcivisync_updatememberwpdb = $wpdb->update('wp_bp_groups_members', array( 'is_confirmed' => 1,),
						array('group_id' => $bpcivisync_bpgroupmembersarr[$y]['group_id'], 'user_id' => $bpcivisync_bpgroupmembersarr[$y]['user_id'],),
						array('%d'));
				//echo "Removed Record";
			} elseif (empty($bpcivi_groupcivimemberstatus[$bpcivisync_bpgroupmembersarr[$y]['group_id']][$bpcivisync_bpgroupmembersarr[$y]['user_id']]) == 1 && $bpcivisync_bpgroupmembersarr[$y]['is_confirmed'] == 0) {
				//echo "Buddypress record not confirmed, user id:  " . $bpcivisync_bpgroupmembersarr[$y]['user_id'] . " For Group " . $bpcivisync_bpgroupmembersarr[$y]['group_id'] . "<br>";
				//Do nothing
			} elseif (isset($bpcivi_groupcivimemberstatus[$bpcivisync_bpgroupmembersarr[$y]['group_id']][$bpcivisync_bpgroupmembersarr[$y]['user_id']]) == 1 && $bpcivisync_bpgroupmembersarr[$y]['is_confirmed'] == 0) {
				$bpcivisync_updatememberwpdb = $wpdb->update('wp_bp_groups_members', array( 'is_confirmed' => 1,),
				array('group_id' => $bpcivisync_bpgroupmembersarr[$y]['group_id'], 'user_id' => $bpcivisync_bpgroupmembersarr[$y]['user_id'],),
				array('%d'));
				//echo "Confirmed Record";
			}
		}
	}
	
//Diagnostic Printouts

} //End Function - remove when first slashed complete.


?>

