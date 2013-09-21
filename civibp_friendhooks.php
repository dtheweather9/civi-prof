<?php


function bpcivi_addfriend($bpcivi_friendshipid,$bpcivi_initfriendwpid,$bpcivi_targfriendwpid) {

//Entry Diagnostics
$bpcivi_emailstring = "Add Friendship:  Friendship ID: " . $bpcivi_friendshipid . " Initiator Friend: " . $bpcivi_initfriendwpid . " Target Friend: " . $bpcivi_targfriendwpid;
	//mail("dmpastuf@seds.org","Friendship Add Test",$bpcivi_emailstring);
//Bootstrap Civicrm
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
//Define Friend Relationship
	$bpcivi_friend_relationship = get_option('civiprof_friendsnum'); 
//Loop up Init Friend CID
	$bpcivifriendinit_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  		'uf_id' => $bpcivi_initfriendwpid,);
	$bpcivifriendinit_result = civicrm_api('UFMatch', 'get', $bpcivifriendinit_params);
	$bpcivifriendinit_cid = $bpcivifriendinit_result['values'][0];
//Loop up Target Friend CID
	$bpcivifriendtarg_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  		'uf_id' => $bpcivi_targfriendwpid,);
	$bpcivifriendtarg_result = civicrm_api('UFMatch', 'get', $bpcivifriendtarg_params);
	$bpcivifriendtarg_cid = $bpcivifriendtarg_result['values'][0];
//GET Relationship Record
	$bpcivi_checkfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'relationship_type_id' => $bpcivi_friend_relationship,
		'contact_id_a' => $bpcivifriendinit_cid,
		'contact_id_b' => $bpcivifriendtarg_cid,);
	$bpcivi_checkfriendshipresult = civicrm_api('Relationship', 'get', $bpcivi_checkfriendshipparams);
	if ($bpcivi_checkfriendshipresult["count"] == 0) {
		//If 0 create with no id
		$bpcivi_newfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'relationship_type_id' => $bpcivi_friend_relationship,
			'contact_id_a' => $bpcivifriendinit_cid,
			'contact_id_b' => $bpcivifriendtarg_cid,
			'is_active' => 1,
			'start_date' => date("Y-m-d"));
		$bpcivi_newfriendshipresult = civicrm_api('Relationship', 'create', $bpcivi_newfriendshipparams);
	} else {
		//If 1 create with ID i.e. update
		$bpcivi_newfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'id' => $bpcivi_checkfriendshipresult["values"][0]["id"],
			'relationship_type_id' => $bpcivi_friend_relationship,
			'contact_id_a' => $bpcivifriendinit_cid,
			'contact_id_b' => $bpcivifriendtarg_cid,
			'is_active' => 1,
			'start_date' => date("Y-m-d"));
		$bpcivi_newfriendshipresult = civicrm_api('Relationship', 'create', $bpcivi_newfriendshipparams);
	}
//Diagnostics
	//mail("dmpastuf@seds.org","Friendship Add Test Result",implode("|",$bpcivi_newfriendshipresult));
}
add_action( 'friends_friendship_accepted', 'bpcivi_addfriend', 10, 3 );

function bpcivi_deletefriend($bpcivi_friendshipid,$bpcivi_initfriendwpid,$bpcivi_targfriendwpid) {

$bpcivi_emailstring = "Delete Friendship:  Friendship ID: " . $bpcivi_friendshipid . " Initiator Friend: " . $bpcivi_initfriendwpid . " Target Friend: " . $bpcivi_targfriendwpid;
	//mail("dmpastuf@seds.org","Friendship Add Test",$bpcivi_emailstring);
//Bootstrap Civicrm
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
//Define Friend Relationship
	$bpcivi_friend_relationship = 11;  //This needs to be converted over to use the database value found in wp-options
//Loop up Init Friend CID
	$bpcivifriendinit_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  		'uf_id' => $bpcivi_initfriendwpid,);
	$bpcivifriendinit_result = civicrm_api('UFMatch', 'get', $bpcivifriendinit_params);
	$bpcivifriendinit_cid = $bpcivifriendinit_result['values'][0];
//Loop up Target Friend CID
	$bpcivifriendtarg_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  		'uf_id' => $bpcivi_targfriendwpid,);
	$bpcivifriendtarg_result = civicrm_api('UFMatch', 'get', $bpcivifriendtarg_params);
	$bpcivifriendtarg_cid = $bpcivifriendtarg_result['values'][0];
//GET Relationship Record
	$bpcivi_checkfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'relationship_type_id' => $bpcivi_friend_relationship,
		'contact_id_a' => $bpcivifriendinit_cid,
		'contact_id_b' => $bpcivifriendtarg_cid,);
	$bpcivi_checkfriendshipresult = civicrm_api('Relationship', 'get', $bpcivi_checkfriendshipparams);
	if ($bpcivi_checkfriendshipresult["count"] == 0) {
		//If 0 create with no id, should never happen
		$bpcivi_newfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'relationship_type_id' => $bpcivi_friend_relationship,
			'contact_id_a' => $bpcivifriendinit_cid,
			'contact_id_b' => $bpcivifriendtarg_cid,
			'is_active' => 0,
			'end_date' => date("Y-m-d"));
		$bpcivi_newfriendshipresult = civicrm_api('Relationship', 'create', $bpcivi_newfriendshipparams);
	} else {
		//If 1 create with ID i.e. update
		$bpcivi_newfriendshipparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'id' => $bpcivi_checkfriendshipresult["values"][0]["id"],
			'relationship_type_id' => $bpcivi_friend_relationship,
			'contact_id_a' => $bpcivifriendinit_cid,
			'contact_id_b' => $bpcivifriendtarg_cid,
			'is_active' => 0,
			'end_date' => date("Y-m-d"));
		$bpcivi_newfriendshipresult = civicrm_api('Relationship', 'create', $bpcivi_newfriendshipparams);
	}
//Diagnostics
	//mail("dmpastuf@seds.org","Friendship Delete Test Result",implode("|",$bpcivi_newfriendshipresult));
}
add_action( 'friends_friendship_deleted', 'bpcivi_deletefriend', 10, 3 );