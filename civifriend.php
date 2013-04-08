<?php
  $bpprof_baseurl = str_replace("/wp-content/plugins/bp-civiprof","", getcwd());
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm.settings.php');
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	include_once($bpprof_baseurl . '/wp-blog-header.php');
add_action('bpcivi_updatefriends_hook','bpcivi_updatefriends()');
function bpcivi_updatefriends() {
//Include Files
	$bpprof_baseurl = str_replace("/wp-content/plugins/bp-civiprof","", getcwd());
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm.settings.php');
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once($bpprof_baseurl . '/wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	include_once($bpprof_baseurl . '/wp-blog-header.php');
	$config = CRM_Core_Config::singleton();
//Get the results from the buddypress file system
	$bpcivi_myrowsdb = $wpdb->get_results( "SELECT * FROM wp_bp_friends" );
//Get the friend relationship type

	$bpcivi_friend_relationship = 11;  //This needs to be converted over to use the database value found in wp-options

//Start for loop for each friendship located on Buddypress
for ($i=0; $i < count($bpcivi_myrowsdb) ; $i++ ) 
{
	$bpcivi_myrows = get_object_vars($bpcivi_myrowsdb[$i]);
//Lookup Cid for both parties
	$params_matchinitiator = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','uf_id' => $bpcivi_myrows['initiator_user_id']);
	$params_matchfriend = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','uf_id' => $bpcivi_myrows['friend_user_id']);
	$bpciviapi_initiator_user_id = civicrm_api('UFMatch', 'get', $params_matchinitiator);
	$bpciviapi_friend_user_id = civicrm_api('UFMatch', 'get', $params_matchfriend);
	$bpcivi_initiator_user_id = $bpciviapi_initiator_user_id['values'][$bpciviapi_initiator_user_id['id']]['contact_id'];
	$bpcivi_friend_user_id = $bpciviapi_friend_user_id['values'][$bpciviapi_friend_user_id['id']]['contact_id'];
//Determine if Friendship exhists in civicrm
    //First find the relationship data using the crm; due to how both store, find both ways
	$bpciviapi_relparam1 = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id_a' => $bpcivi_initiator_user_id,
		'contact_id_b' => $bpcivi_friend_user_id,
		'relationship_type_id' => $bpcivi_friend_relationship,);
	$bpciviapi_relparam2 = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id_a' => $bpcivi_friend_user_id,
		'contact_id_b' => $bpcivi_initiator_user_id,
		'relationship_type_id' => $bpcivi_friend_relationship,);
	$bpciviapi_rel1 = civicrm_api('Relationship', 'get', $bpciviapi_relparam1);
	$bpciviapi_rel2 = civicrm_api('Relationship', 'get', $bpciviapi_relparam2);
//Find in Civicrm if the friendships are active
		unset($bpcivi_clractive);
		unset($bpcivi_crlactive);
	if ($bpciviapi_rel1['values'][0]['is_active'] == 1) {
		$bpcivi_clractive = 1;
	} else {
		$bpcivi_clractive = 0;
	}
	if ($bpciviapi_rel2['values'][0]['is_active'] == 1) {
		$bpcivi_crlactive = 1;
	} else {
		$bpcivi_crlactive = 0;
	}
//Determine which Civicrm friendship order to use - this should now be equal to initiator's civicrm id.
//If the value of the array where the friendship is stored isn't 0 this will result in no data - problem.
	//Unset then load
		unset($bpcivi_contact_id_a);
		unset($bpcivi_contact_id_b);
		unset($bpcivi_update_id);
		unset($bpcivi_cfrActive);
	//Now Run Ifs
	if ( ($bpcivi_crlactive == 0) && ($bpcivi_clractive == 0)) {
		//No Friendship is active in Civicrm
			$bpcivi_cfrActive = 0;
	} elseif ( ($bpcivi_clractive == 1) && ($bpcivi_crlctive == 0)) {
		//Use Left to Right in Civicrm
			$bpcivi_cfrActive = 1;
			$bpcivi_contact_id_a = intval($bpciviapi_rel1['values'][0]['contact_id_a']);
			$bpcivi_contact_id_b = intval($bpciviapi_rel1['values'][0]['contact_id_b']);
			$bpcivi_update_id = intval($bpciviapi_rel1['values'][0]['id']);
	} elseif ( ($bpcivi_clractive == 0) && ($bpcivi_crlactive == 1)) {
		//Use Right to Left in Civicrm 
			$bpcivi_cfrActive = 1;
			$bpcivi_contact_id_a = intval($bpciviapi_rel2['values'][0]['contact_id_a']);
			$bpcivi_contact_id_b = intval($bpciviapi_rel2['values'][0]['contact_id_b']);
			$bpcivi_update_id = intval($bpciviapi_rel2['values'][0]['id']);
	} elseif ( ($bpcivi_clractive == 1) && ($bpcivi_crlactive == 1)) {
		//Both are active; use the Left
			$bpcivi_cfrActive = 1;
			$bpcivi_contact_id_a = intval($bpciviapi_rel1['values'][0]['contact_id_a']);
			$bpcivi_contact_id_b = intval($bpciviapi_rel1['values'][0]['contact_id_b']);
			$bpcivi_update_id = intval($bpciviapi_rel1['values'][0]['id']);
	} else {
			$bpcivi_cfrActive = 4;
	}	
//Program now has if the friendship is active for both buddypress and for civicrm; therefore we can perform a if matrix to set the output of the function.
	if (($bpcivi_myrows['is_confirmed']==0) && ($bpcivi_cfrActive == 0)) {
		//Friendship is inactive; can basically ignore this record
		$bpcivi_frstatus =  "Friendship is inactive -  can basically ignore this record<br>";
	} elseif (($bpcivi_myrows['is_confirmed']==1) && ($bpcivi_cfrActive == 0)) {
		//The Friendship is new or the friendship has been cancelled and now re-newed
			if ( (count($bpciviapi_rel1['values'][0]) > 0) || (count($bpciviapi_rel2['values'][0]) > 0)) {
				//Re-Friend Request
				$bpcivi_frstatus =  "<p>The Friendship has been cancelled and now re-newed<br> Re-Friend Request 1</p>";
				$bpcivi_refriendparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id_a' => $bpcivi_initiator_user_id,'contact_id_b' => $bpcivi_friend_user_id,'relationship_type_id' => $bpcivi_friend_relationship,'is_active' => 1,'id' => $bpcivi_update_id,);
				$bpcivi_refriendresult = civicrm_api('Relationship', 'update', $bpcivi_refriendparams);
				$bpcivi_server = array('time' => microtime(),'contact_id_a' => $bpcivi_initiator_user_id,'contact_id_b' => $bpcivi_contact_id_b,'is_active' => 1,);
				$wpdb->insert(wp_bp_civi_friendsync,$bpcivi_server);
			} else {
				//New Friend Request
				$bpcivi_frstatus =  "<p>The Friendship is new</p>";
				$bpcivi_newfriendparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id_a' => $bpcivi_initiator_user_id,'contact_id_b' => $bpcivi_friend_user_id,'relationship_type_id' => $bpcivi_friend_relationship,'is_active' => 1,);
				$bpcivi_newfriendresult = civicrm_api('Relationship', 'create', $bpcivi_newfriendparams);
				$bpcivi_server = array('time' => microtime(),'contact_id_a' => $bpcivi_contact_id_a,'contact_id_b' => $bpcivi_contact_id_b,'is_active' => 1,);
				$wpdb->insert(wp_bp_civi_friendsync,$bpcivi_server);
				}
	} elseif (($bpcivi_myrows['is_confirmed']==1) && ($bpcivi_cfrActive == 1)) {
		//The Friendship is active in both buddypress and civicrm - nothing must be done
		$bpcivi_frstatus = "The Friendship is active in both buddypress and civicrm - nothing must be done<br>";
	} elseif (($bpcivi_myrows['is_confirmed']==0) && ($bpcivi_cfrActive == 1)) {
		//The Friendship has been cancelled; must make record inactive in civicrm update
		$bpcivi_frstatus = "The Friendship has been cancelled; must make record inactive in civicrm update<br>";
				$bpcivi_unfriendparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id_a' => $bpcivi_initiator_user_id,'contact_id_b' => $bpcivi_friend_user_id,'relationship_type_id' => $bpcivi_friend_relationship,'is_active' => 0,'id' => $bpcivi_update_id,);
				$bpcivi_unfriendresult = civicrm_api('Relationship', 'update', $bpcivi_unfriendparams);
				$bpcivi_server = array('time' => microtime(),'contact_id_a' => $bpcivi_initiator_user_id,'contact_id_b' => $bpcivi_friend_user_id,'is_active' => 0,);
				echo "start write";
				$wpdb->insert(wp_bp_civi_friendsync,$bpcivi_server,array('%s','%s','%s','%s'));
				echo "end write";
	}

//Print Out - Diagnostic
/*	echo "<p><h2> Posting </h2></p>";
	echo "Count: " . $i ;
	echo " | " . $bpcivi_myrows['initiator_user_id'] . " | " . $bpcivi_myrows['friend_user_id'] . " |" . $bpcivi_myrows['is_confirmed'] . "|" ;
	echo "Init Contact ID: " . $bpcivi_initiator_user_id . "|";
	echo "Friend Contact ID: " . $bpcivi_friend_user_id  . "|";
	echo "LR_A: " . $bpcivi_clractive  . "|";
	echo "RL_A: " . $bpcivi_crlactive  . "|<br>";
	//Status from Earlier component
	echo $bpcivi_frstatus;
	echo "<p> Update ID is :" . $bpcivi_update_id . "</p>";
	echo "<p>Echo Civic Friendship Exhists: " . $bpcivi_cfrActive . "<br>";
	echo "Echo Civic Contact A: " . $bpcivi_contact_id_a . "<br>";
	echo "Echo Civic Contact B: " . $bpcivi_contact_id_b . "<br>";
	echo "Echo Update ID Post Use Order: " . $bpcivi_update_id . "<br></p>";
	echo "New Friend Result: <pre>"; print_r($bpcivi_newfriendresult); echo "</pre>";
	echo "New Friend Param: <pre>"; print_r($bpcivi_newfriendparams); echo "</pre>";
	echo "Re Friend Result: <pre>"; print_r($bpcivi_refriendresult); echo "</pre>";
	echo "UnFriend Result: <pre>"; print_r($bpcivi_unfriendresult); echo "</pre>";*/

}  //End of if Loop for each friendship
mail('dmpastuf@seds.org', 'Civifriend Job', 'Civifriend.php has sucessfully run');
} //End Function
// Update ID; 

?>

