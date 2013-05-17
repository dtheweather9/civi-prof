<?php


add_action('bp_init', 'bpcivi_addgroupeditnav1');

function bpcivi_addgroupeditnav1() {
if ( class_exists( 'BP_Group_Extension' ) ) { // Recommended, to prevent problems during upgrade or when Groups are disabled
 
    class BPCivigroupedit extends BP_Group_Extension {
 		 function __construct() {
        	$this->name = 'Edit Chapter Info';
            $this->slug = 'bpcivi-groupedit';
            $this->create_step_position = 2;
            $this->nav_item_position = 2;
            $this->visibility = 'private';
            $this->enable_nav_item = false;
		}
        /**
         * The content of the My Group Extension tab of the group admin
         */
	function edit_screen() {
	    if ( !bp_is_group_admin_screen( $this->slug ) )
			return false;
    //Include Files
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
	global $bp;
	//Current Buddypress Group
		$bpcivi_currgroup = $bp->groups->current_group->id;
	//Run Query on DB
		$bpvivi_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $bpcivi_currgroup;
		$bpcivisync_settinggroups = $wpdb->get_results($bpvivi_querytext);
	//Assign to array from first membership found - oldest set effectively
		$bpcivi_groupsettings = get_object_vars($bpcivisync_settinggroups[0]);
	//Form Reaction TODO
		if (isset($_POST['groupeditsubmit'])) {
		//Contact Update
		$bpcivi_groupupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'id' => $bpcivi_groupsettings['orgid'],
		'organization_name' => $_POST['orgname'],
		'legal_name' => $_POST['legalname'],
		'nick_name' =>$_POST['nickname'],
		);
		$bpcivi_groupeditpostresult = civicrm_api('Contact', 'create', $bpcivi_groupupdateparams);
		//Address Update 
		$bpcivi_groupaddressupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'id' => $_POST['addressid'],
		'street_address' => $_POST['street1'],
		'supplemental_address_1' => $_POST['street2'],
		'supplemental_address_2' => $_POST['street3'],
		'city' => $_POST['city1'],
		'geo_code_1' => $_POST['latitude'],
		'geo_code_2' => $_POST['longitude'],
		'state_province_id' => $_POST['state'],
		);
		$bpcivi_groupaddresseditpostresult = civicrm_api('Address', 'create', $bpcivi_groupaddressupdateparams);
		//Website Update
		$bpcivi_groupwebupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'id' => $_POST['webid'],
		'url' => $_POST['website1'],
		);
		$bpcivi_groupwebupdateresult = civicrm_api('Website', 'create', $bpcivi_groupwebupdateparams);
		
		/*//Diagnostics
		echo "Being Sent to API - Address: <pre>";
		print_r($bpcivi_groupwebupdateresult);
		echo "<pre>";*/
		}
	
	
	
	//Run Query API Against Group
		$bpcivi_groupeditparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $bpcivi_groupsettings['orgid'],);
		$bpcivi_groupeditresult = civicrm_api('Contact', 'get', $bpcivi_groupeditparams);
		 //Array of assignments	
			$bpcivi_groupedit_orgname = $bpcivi_groupeditresult['values'][0]['organization_name'];
			$bpcivi_groupedit_legalname = $bpcivi_groupeditresult['values'][0]['legal_name'];
			$bpcivi_groupedit_nickname = $bpcivi_groupeditresult['values'][0]['nick_name'];
			$bpcivi_groupedit_streetaddress = $bpcivi_groupeditresult['values'][0]['street_address'];
			$bpcivi_groupedit_supplemental_address_1 = $bpcivi_groupeditresult['values'][0]['supplemental_address_1'];
			$bpcivi_groupedit_supplemental_address_2 = $bpcivi_groupeditresult['values'][0]['supplemental_address_2'];
			$bpcivi_groupedit_city = $bpcivi_groupeditresult['values'][0]['city'];
			$bpcivi_groupedit_geo_code_1 = $bpcivi_groupeditresult['values'][0]['geo_code_1'];
			$bpcivi_groupedit_geo_code_2 = $bpcivi_groupeditresult['values'][0]['geo_code_2'];
			$bpcivi_groupedit_state_province_id = $bpcivi_groupeditresult['values'][0]['state_province_id'];
			$bpcivi_groupedit_country_id = $bpcivi_groupeditresult['values'][0]['country_id'];
			
	//Organization Website Query
		$bpcivi_groupeditwebsiteparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $bpcivi_groupsettings['orgid'],);
		$bpcivi_groupeditwebsiteresult = civicrm_api('Website', 'get', $bpcivi_groupeditwebsiteparams);
		$bpcivi_groupedit_website1 = $bpcivi_groupeditwebsiteresult['values'][0]['url'];
	//Get the states list
		$bpcivi_statesparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','name' => 'stateProvince',);
		$bpcivi_statesresult = civicrm_api('Constant', 'get', $bpcivi_statesparams);
		$bpcivi_statesresultarr = $bpcivi_statesresult['values'];
		$bpcivi_statesresultarrkeyd = array_values($bpcivi_statesresultarr);
		$bpcivi_statesresultarrkeys = array_keys($bpcivi_statesresultarr);
	//Get the Countries list
		$bpcivi_countriesparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','name' => 'country',);
		$bpcivi_countriesresult = civicrm_api('Constant', 'get', $bpcivi_countriesparams);
		$bpcivi_countriesresultarr = $bpcivi_countriesresult['values'];
		$bpcivi_countriesresultarrkeyd = array_values($bpcivi_countriesresultarr);
		$bpcivi_countriesresultarrkeys = array_keys($bpcivi_countriesresultarr);
	//Display Form
		echo '<div id="bpcivigroupeditform">';
		echo '<form action="" method="post">';
		echo '<input type="hidden" name="addressid" value="' . $bpcivi_groupeditresult['values'][0]['address_id'] . '">';
		echo '<input type="hidden" name="webid" value="' . $bpcivi_groupeditwebsiteresult['values'][0]['id'] . '">';
		echo '<table border=1>';
		echo "<tr><td>" . "Organization Name" . "</td><td>" . '<input type="text" name="orgname" value="' .$bpcivi_groupedit_orgname  . '"></td><tr>';
		echo "<tr><td>" . "Legal Name" . "</td><td>" . '<input type="text" name="legalname" value="' .$bpcivi_groupedit_legalname  . '"></td><tr>';
		echo "<tr><td>" . "Nickname" . "</td><td>" . '<input type="text" name="nickname" value="' .$bpcivi_groupedit_nickname  . '"></td><tr>';
		echo "<tr><td>" . "Website" . "</td><td>" . '<input type="url" name="website1" value="' .$bpcivi_groupedit_website1  . '"></td><tr>';
		echo "<tr><td>" . "Street Address" . "</td><td>" . '<input type="text" name="street1" value="' .$bpcivi_groupedit_streetaddress  . '"></td><tr>';
		echo "<tr><td>" . "Street Address 2" . "</td><td>" . '<input type="text" name="street2" value="' .$bpcivi_groupedit_supplemental_address_1  . '"></td><tr>';
		echo "<tr><td>" . "Street Address 3" . "</td><td>" . '<input type="text" name="street3" value="' .$bpcivi_groupedit_supplemental_address_2  . '"></td><tr>';
		echo "<tr><td>" . "City" . "</td><td>" . '<input type="text" name="city1" value="' .$bpcivi_groupedit_city  . '"></td><tr>';
		echo "<tr><td>" . "Latitude" . "</td><td>" . '<input type="text" name="latitude" value="' .$bpcivi_groupedit_geo_code_1  . '"></td><tr>';
		echo "<tr><td>" . "Longitude" . "</td><td>" . '<input type="text" name="longitude" value="' .$bpcivi_groupedit_geo_code_2  . '"></td><tr>';
		echo "<tr><td>" . "State" . "</td><td>" . '<select name="state">';
		for ($i=0;$i<count($bpcivi_statesresultarr);$i++) {
			if ($bpcivi_statesresultarrkeys[$i] == $bpcivi_groupedit_state_province_id) {
			echo '<option value="' . $bpcivi_statesresultarrkeys[$i] . '" selected>' . $bpcivi_statesresultarrkeyd[$i] . '</option>';	
			} else {
			echo '<option value="' . $bpcivi_statesresultarrkeys[$i] . '">' . $bpcivi_statesresultarrkeyd[$i] . '</option>';
			}
		}
		echo '</select></td><tr>';
		//value="' .$bpcivi_groupedit_state_province_id  . '"
		echo "<tr><td>" . "Country" . "</td><td>" . '<input type="text" name="orgname" disabled value="' .$bpcivi_countriesresultarr[$bpcivi_groupedit_country_id]  . '"></td><tr>';
		echo '<tr><td colspan="2">' . '<input id="bpedit_submit" type="submit" name="groupeditsubmit" value="Submit">' . '</td></tr>';
		echo "</table></form>";
		echo "</div>";
	//Diagnostics
	/*
		echo "<br>Post: <pre>";
		print_r($_POST);
		echo "</pre>";
        echo "<br>API Call: <pre>";
		print_r($bpcivi_groupeditwebsiteresult);
		echo "</pre>";        
		*/
        }

}
bp_register_group_extension( 'BPCivigroupedit' );
}
}

/* //Currently Hidden as it dosn't work
add_action('bp_init', 'bpcivi_addgroupeditnav');

function bpcivi_addgroupeditnav() {
//Add Array
  global $bp;
	$bpcivi_groupeditparent_url = trailingslashit( $bp->canonical_stack->base_url . 'admin' );
	//$bpcivi_groupeditparent_url = trailingslashit( $bp->groups->current_group->url . 'profile' );
	$bpcivi_groupeditdefaults = array(
		'name'            => 'Group Edit', // Display name for the nav item
		'slug'            => 'gedit', // URL slug for the nav item
		'parent_slug'     => 'admin', // URL slug of the parent nav item
		'parent_url'      => $bpcivi_groupeditparent_url, // URL of the parent item
		'item_css_id'     => bpcivi_imagecss, // The CSS ID to apply to the HTML of the nav item
		'user_has_access' => true,  // Can the logged in user see this nav item?
		'position'        => 90,    // Index of where this nav item should be positioned
		'screen_function' => bpcivi_groupedit_page, // The name of the function to run when clicked
	);

bp_core_new_subnav_item($bpcivi_groupeditdefaults);
add_action('bp_template_content', 'bpcivi_groupedit_page_content');
}

function bpcivi_groupedit_page() {
	bp_core_load_template( 'members/single/plugins' ); //Loads general members/single/plugins template
}

function bpcivi_groupedit_page_content() {
	global $bp;
	if ($bp->current_action == 'gedit' ) {  //If the Action 
		bpcivi_groupeditnavpage();  
	}
}

function bpcivi_groupeditnavpage() {
	global $bp;
	
	echo "This is the group image nav page<p>";
	echo "Parent URL: " . $bp->loggedin_user->domain . $bp->groups->slug . '/' . '<br>';
	echo "Parent Slug: " . $bp->groups->slug . '<br>';
   	

	echo "<pre>";
	print_r($bp);
	echo "<pre>";
}

function bpcivi_groupedit_test() {
	global $bp;
	echo "<pre>";
	print_r($bp);
	echo "<pre>";
}
add_action('bp_after_group_admin_content', 'bpcivi_groupedit_test');
add_action('bp_after_profile_loop_content', 'bpcivi_groupedit_test');//Just for Profile Check
*/