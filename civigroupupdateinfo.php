<?php


add_action('bp_init', 'bpcivi_addgroupeditnav1');

function bpcivi_addgroupeditnav1() {
if ( class_exists( 'BP_Group_Extension' ) ) { // Recommended, to prevent problems during upgrade or when Groups are disabled
  //Run to find out if a chapter
 	global $wpdb;
	global $bp;
 	$bpcivi_ck_currgroup = $bp->groups->current_group->id;
 	if(is_numeric($bpcivi_ck_currgroup)) { //Check if the group is set
 		$bpcivi_ck_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $bpcivi_ck_currgroup;
		$bpcivi_ck_settinggroups = $wpdb->get_results($bpcivi_ck_querytext);
 	}
    class BPCivigroupedit extends BP_Group_Extension {
 		 function __construct() {
        	$this->name = 'Edit Chapter Info';
            $this->slug = 'bpcivi-groupedit';
            $this->nav_item_position = 2;
            $this->visibility = 'private';
            $this->enable_nav_item = false;
            $this->enable_create_step = false;
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
		//echo plugins_url() . "/civi-prof/posts/groupupdateinfopost.php" . "<br>";
		echo '<div id="bpcivigroupeditform">';
		echo '<form action="" method="post">';
		echo '</form>';
		echo '<form action="' . plugins_url() . "/civi-prof/posts/groupupdateinfopost.php" . '" method="post">';
		echo '<input type="hidden" name="orgid" value="' . $bpcivi_groupsettings['orgid'] . '">';
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
		echo '<input type="hidden" name="infoupdateredirect_url" value="' . get_site_url() . $_SERVER["REQUEST_URI"] . '">';
		wp_nonce_field("civiprofgroupupdate", "_civiprofgroupupdate");
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
		//echo "Test Change";
        }

}
if(count($bpcivi_ck_settinggroups) > 0) { //Make it so that the group exension is only used for chapter
	bp_register_group_extension( 'BPCivigroupedit' );
}
}
}