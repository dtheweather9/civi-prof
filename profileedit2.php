<?php

add_action('bp_after_profile_edit_content','bpcivi_editprof3');

function bpcivi_editprof3() {
  	
  	//End Devel
  	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
//Get the Profile Number
	$bpcivi_edit_profile_id = get_option('civiprof_editprofnum');
//Get the Wordpress to Civicrm ID
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	  'uf_id' => get_current_user_id(),);
	$bpcivi_edituserresult = civicrm_api('UFMatch', 'get', $params);
	$bpcivi_editprofcid = $bpcivi_edituserresult['values'][min(array_keys($bpcivi_edituserresult['values']))]['contact_id'];
//Respond to the post
	if (wp_verify_nonce( $_POST['_bpeditnonce'], 'bpcivi-edit' )==1) {
	
		//Get All values
			$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'contact_id' => $bpcivi_editprofcid,
				);
			$bpcivi_contactvaluesresult = civicrm_api('Contact', 'getsingle', $params);
			$bpcivi_contactvaluesresult = array_keys($bpcivi_contactvaluesresult);
		//Create variables array by running through the current values
			for ($i=0;$i<count($bpcivi_contactvaluesresult);$i++) {
				if(isset($_POST[$bpcivi_contactvaluesresult[$i]])) {
					$bpcivi_contactupdateprep[$bpcivi_contactvaluesresult[$i]] = $_POST[$bpcivi_contactvaluesresult[$i]];
				}
			}
		//Update/Create parameters
			$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'custom_id' => $bpcivi_editprofcid,
				'id' => $bpcivi_editprofcid,
				'contact_type' => "Individual",);
			$bpcivi_contactupdateprep = array_merge($params,$bpcivi_contactupdateprep) ;
		//Update the custom data
			$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'entity_id' => $bpcivi_editprofcid,	);
			$bpcivi_getcustomresult = civicrm_api('CustomValue', 'get', $params);
			$bpcivi_getcustomresult = $bpcivi_getcustomresult['values'];
			for($i=0;$i<count($bpcivi_getcustomresult);$i++) {
				//$bpcivi_customnumber[$i] = $bpcivi_getcustomresult[$i]['id'];
				$bpcivi_customname = "custom_" . $bpcivi_getcustomresult[$i]['id'];
				$bpcivi_customarr[$bpcivi_customname] = $_POST[$bpcivi_customname];
			}
			$bpcivi_contactupdateprep = array_merge($bpcivi_contactupdateprep,$bpcivi_customarr) ;
		//Execute update	
			$bpcivi_postresult = civicrm_api('Contact', 'create', $bpcivi_contactupdateprep);
		//Update Email
		if (isset($_POST['email_id']) && isset($_POST['email'])) {
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['email_id'],
					'email' => $_POST['email'],
					'is_primary' => 1,
					'location_type_id' => 1,
					);
				$bpcivi_emailupdateresult = civicrm_api('Email', 'create', $params);	
			}
		//Update Address
			if (isset($_POST['address_id'])) {
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['address_id'],	);
				$bpcivi_addressvaluesresult = civicrm_api('Address', 'getsingle', $params);
				$bpcivi_addressvaluesresult = array_keys($bpcivi_addressvaluesresult);
				for($i=0;$i<count($bpcivi_addressvaluesresult);$i++) {
					$bpcivi_addressupdateprep[$bpcivi_addressvaluesresult[$i]] = $_POST[$bpcivi_addressvaluesresult[$i]];
				}
				$bpcivi_addressupdateprep = array_filter($bpcivi_addressupdateprep);
				//Prep  api params
					$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'contact_id' => $_POST['cid'],
					'location_type_id' => 1,
					'id' => $_POST['address_id'],	);
				$bpcivi_addressupdateprep = array_merge($params,$bpcivi_addressupdateprep);
				$bpcivi_addressupdateresult = civicrm_api('Address', 'create', $bpcivi_addressupdateprep);
			}
	} elseif(count($_POST) > 1) {
		echo "Security violation - please try again";	
	}
	
//Get the profile information
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest',  'sequential' => 1,
	'id' => $bpcivi_edit_profile_id, );
	$bpcivi_profileresult = civicrm_api('UFGroup', 'get', $params);
//Get the profile field information
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'uf_group_id' => $bpcivi_edit_profile_id,
		'options'  => array( 'sort'  => 'weight ASC', ));
	$bpcivi_uffieldresult = civicrm_api('UFField', 'get', $params);
	$bpcivi_uffieldresult = $bpcivi_uffieldresult['values'];
//Custom Data Types
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,);
	$bpcivi_customresult = civicrm_api('CustomField', 'get', $params);
	$bpcivi_customresult = $bpcivi_customresult['values'];
	for($j=0;$j<count($bpcivi_customresult); $j++) {
		if(strlen($bpcivi_customresult[$j]['option_group_id']) >0) {
			//Select or option Type
			$bpcivi_customoptionsarray[$bpcivi_customresult[$j]['id']] = $bpcivi_customresult[$j]['option_group_id'];
		} else {
			//Regular type
			$bpcivi_customoptionsarray[$bpcivi_customresult[$j]['id']] = NULL;
		}
		$bpcivi_customarray[$bpcivi_customresult[$j]['id']] = $bpcivi_customresult[$j]['data_type'];
	}
//Get the states list
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','name' => 'stateProvince',);
	$bpcivi_statesresult = civicrm_api('Constant', 'get', $params);
	$bpcivi_statesresultarr = $bpcivi_statesresult['values'];
	$bpcivi_statesresultarrkeyd = array_values($bpcivi_statesresultarr);
	$bpcivi_statesresultarrkeys = array_keys($bpcivi_statesresultarr);
//Get the Countries list
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','name' => 'country',);
	$bpcivi_countriesresult = civicrm_api('Constant', 'get', $params);
	$bpcivi_countriesresult = $bpcivi_countriesresult['values'];
	$bpcivi_countriesresultarrkeyd = array_values($bpcivi_countriesresult);
	$bpcivi_countriesresultarrkeys = array_keys($bpcivi_countriesresult);
//Get Genders
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'option_group_id' => 3,//Gender
		'options'  => array( 'sort'  => 'value ASC', ));
	$bpcivi_genderresult = civicrm_api('OptionValue', 'get', $params);
	$bpcivi_genderresult = $bpcivi_genderresult['values'];
//Get Contact Data
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $bpcivi_editprofcid, );
	$bpcivi_contactresult = civicrm_api('Contact', 'getsingle', $params);
//Get Custom Data for user
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'entity_id' => $bpcivi_editprofcid, );
	$bpcivi_contactcustomresult = civicrm_api('CustomValue', 'get', $params);
	$bpcivi_contactcustomresult = $bpcivi_contactcustomresult['values'];
	for($j=0;$j<count($bpcivi_contactcustomresult);$j++) {
		$bpcivi_contactcustom1[$bpcivi_contactcustomresult[$j]['id']] = $bpcivi_contactcustomresult[$j]['latest'];
	}
	
//Form
	echo '<div id="bpcivieditform">';
	echo "<h4>" . $bpcivi_profileresult['values'][0]['title'] . "</h4><br>";
	echo '<div id="bpcivieditpreinfo">' . $bpcivi_profileresult['values'][0]['help_pre'] . "</div>";
	echo '<form action="" name="bpcivi-editprofileform" method="post"><table border="1">';
		
		echo '<input type="hidden" name="_bpeditnonce" value="' . wp_create_nonce('bpcivi-edit') . '">';
		echo '<input type="hidden" name="cid" value="' . $bpcivi_editprofcid . '">';
		echo "<tr>";
			echo "<td>"; echo "Member Number"; echo "</td>";
			echo "<td>"; echo $bpcivi_editprofcid; echo "</td>";
		echo "</tr>";
	for($i=0;$i<count($bpcivi_uffieldresult);$i++) {
		echo "<tr>";
		if(strlen($bpcivi_uffieldresult[$i]['help_pre']) > 1) {echo '<tr><td colspan="2" style="border-bottom: none;">'; echo $bpcivi_uffieldresult[$i]['help_pre'];  echo "</td></tr>"; }
		echo "<td>";
			echo $bpcivi_uffieldresult[$i]['label'];
		echo "</td>";
		if($bpcivi_uffieldresult[$i]['field_name'] == "gender") {
			echo "<td>";
			for ($j=0; $j<count($bpcivi_genderresult); $j++) {
				if ($bpcivi_contactresult['gender_id'] == $bpcivi_genderresult[$j]['value']) { //Selected
					echo '<input type="radio" name="'. $bpcivi_uffieldresult[$i]['field_name'] .'" value="' . $bpcivi_genderresult[$j]['value'] . '" checked>' . $bpcivi_genderresult[$j]['label'] . '<br>';
				} else {  //Regular
					echo '<input type="radio" name="'. $bpcivi_uffieldresult[$i]['field_name'] .'" value="' . $bpcivi_genderresult[$j]['value'] . '">' . $bpcivi_genderresult[$j]['label'] . '<br>'; 
				}
			}	
			echo "</td>";
		} elseif ($bpcivi_uffieldresult[$i]['field_name'] == "birth_date") {
			$tempdate = explode(" ",$bpcivi_contactresult[$bpcivi_uffieldresult[$i]['field_name']]);
						echo "<td>"; echo '<input type="date" name="' . $bpcivi_uffieldresult[$i]['field_name'] . '" value="' . $tempdate[0] . '">'; echo "</td>";
		} elseif ($bpcivi_uffieldresult[$i]['field_name'] == "phone") {
						echo "<td>"; echo '<input type="tel" name="' . $bpcivi_uffieldresult[$i]['field_name'] . '" value="' . $bpcivi_contactresult[$bpcivi_uffieldresult[$i]['field_name']] . '">'; echo "</td>";				
		} elseif ($bpcivi_uffieldresult[$i]['field_name'] == "state_province") {
			echo "<td>"; echo '<select name="' . $bpcivi_uffieldresult[$i]['field_name'] . '">';
			for ($j=0; $j<count($bpcivi_statesresultarrkeyd); $j++) {
				if ($bpcivi_statesresultarrkeys[$j] == $bpcivi_contactresult['state_province_id']) {
					echo '<option value="' . $bpcivi_statesresultarrkeys[$j] . '" selected>'.$bpcivi_statesresultarrkeyd[$j].'</option>';
				} else {
					echo '<option value="' . $bpcivi_statesresultarrkeys[$j] . '">'.$bpcivi_statesresultarrkeyd[$j].'</option>';
				}
			}
			echo '</select>'; echo "</td>";
		} elseif ($bpcivi_uffieldresult[$i]['field_name'] == "country") {
			echo "<td>"; echo '<select name="' . $bpcivi_uffieldresult[$i]['field_name'] . '">';
			for ($j=0; $j<count($bpcivi_countriesresultarrkeys); $j++) {
				if ($bpcivi_countriesresultarrkeys[$j] == $bpcivi_contactresult['country_id']) {
					echo '<option value="' . $bpcivi_countriesresultarrkeys[$j] . '" selected>'.$bpcivi_countriesresultarrkeyd[$j].'</option>';
				} else {
					echo '<option value="' . $bpcivi_countriesresultarrkeys[$j] . '">'.$bpcivi_countriesresultarrkeyd[$j].'</option>';
				}
			}
			echo '</select>'; echo "</td>";
		} else {  //String or Custom
			if (strpos($bpcivi_uffieldresult[$i]['field_name'],'custom_') === 0 || strpos($bpcivi_uffieldresult[$i]['field_name'],'custom_') >= 1 ) { //Custom Data
				$bpcivi_customid = str_replace("custom_","",$bpcivi_uffieldresult[$i]['field_name']);
				if(!is_null($bpcivi_customoptionsarray[$bpcivi_customid])) { //If its an option group
					echo "<td>";
					$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
						'option_group_id' => $bpcivi_customoptionsarray[$bpcivi_customid],);
					$bpcivi_customoptionresult = civicrm_api('OptionValue', 'get', $params);
					$bpcivi_customoptionresult = $bpcivi_customoptionresult['values'];
						echo '<select name="' . $bpcivi_uffieldresult[$i]['field_name'] . '">';
							for ($j=0;$j<count($bpcivi_customoptionresult);$j++) {
									if($bpcivi_contactcustom1[$bpcivi_customid] == $bpcivi_customoptionresult[$j]['value']) {
										echo '<option value="' . $bpcivi_customoptionresult[$j]['value'] . '" selected>'. $bpcivi_customoptionresult[$j]['label'] .'</option>';
									} else {
										echo '<option value="' . $bpcivi_customoptionresult[$j]['value'] . '">'. $bpcivi_customoptionresult[$j]['label'] .'</option>';
									}
								}
						echo "</select>";
					echo "</td>";
				} else {  //Not an option group
					if($bpcivi_customarray[$bpcivi_customid] == "Date") { //Its a date
					$tempdate = explode(" ",$bpcivi_contactcustom1[$bpcivi_customid]);
						echo "<td>"; echo '<input type="date" name="' . $bpcivi_uffieldresult[$i]['field_name'] . '" value="' . $tempdate[0] . '">'; echo "</td>";
					}
				}
			} else { //String
				echo "<td>"; echo '<input type="text" name="' . $bpcivi_uffieldresult[$i]['field_name'] . '" value="' . $bpcivi_contactresult[$bpcivi_uffieldresult[$i]['field_name']] . '">'; echo "</td>";
				
			}
		}
		if(strlen($bpcivi_uffieldresult[$i]['help_post']) > 1) {echo '<tr><td colspan="2" style="border-top: none;">'; echo $bpcivi_uffieldresult[$i]['help_post'];  echo "</td></tr>"; }
		echo "</tr>";
	}
		echo '<input type="hidden" name="' . "address_id" . '" value="' . $bpcivi_contactresult["address_id"] . '">';
		echo '<input type="hidden" name="' . "email_id" . '" value="' . $bpcivi_contactresult["email_id"] . '">';
	echo '</table>';
	echo '<div id="bpcivieditppostinfo">' . $bpcivi_profileresult['values'][0]['help_post'] . "</div>";
	echo '<input type="submit">';
	echo '</form>';
	echo "</div>";
//Diagnostics
	echo '<div class="clear"></div>';
	//echo "nonce: " . wp_verify_nonce( $_POST['wponeonce'], 'bpciviedit' ) . "<br>";

	/*
	echo "POST<pre>";
	print_r($_POST);
	echo "</pre>";
	echo "bpcivi_addressvaluesresult<pre>";
	print_r($bpcivi_addressvaluesresult);
	echo "</pre>";
	echo "bpcivi_addressupdateresult<pre>";
	print_r($bpcivi_addressupdateresult);
	echo "</pre>";
	*/
	
}
?>
