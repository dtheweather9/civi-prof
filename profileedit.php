<?php

function checkDate2($mydate) { 
 //Edit here if US Style Dates are not used
    list($mm,$dd,$yy)=explode("/",$mydate); 
    if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) 
    { 
        return checkdate($mm,$dd,$yy); 
    } 
    return false;            
} 

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

add_action('bp_after_profile_edit_content','bpcivi_editprof2');
add_action('bp_before_profile_edit_content','bpcivi_prediv');

function bpcivi_prediv() {
echo '<div id="bpcivi_bpeditdiv">';
}

function bpcivi_editprof2() {
  include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
	echo "</div>";
//Get the Profile Number
	$bpcivi_edit_profile_id = 17;
//Get the Wordpress to Civicrm ID
	$bpcivi_edituserparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	  'uf_id' => get_current_user_id(),);
	$bpcivi_edituserresult = civicrm_api('UFMatch', 'get', $bpcivi_edituserparams);
	$bpcivi_editprofcid = $bpcivi_edituserresult['values'][min(array_keys($bpcivi_edituserresult['values']))]['contact_id'];
//Post Operations - check nonce
if (count($_POST) > 0 ) {
if (! wp_verify_nonce($_POST['wponeonce'], 'my-nonce') ) die("Security check");
}
//Get Address Number
	$bpcivi_getaddressparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id' => $bpcivi_editprofcid);
	$bpcivi_getaddressresult = civicrm_api('Contact', 'get', $bpcivi_getaddressparams);
	$bpcivi_addressnum = $bpcivi_getaddressresult['values'][0]['address_id'];
	$bpcivi_webaddressnum = $bpcivi_getaddressresult['values'][0]['address_id'];
//Get the Custom Fields
	$bpcivi_customfieldparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest',);
	$bpcivi_customfieldresult = civicrm_api('CustomField', 'get', $bpcivi_customfieldparams);
//Post Form
	if (count($_POST) > 0 ) {
	  if (! wp_verify_nonce($_POST['wponeonce'], 'my-nonce') ) die("Security check");
	  $bpcivi_profileapipost = $_POST;
	  $bpcivi_profileapikeys = array_keys($bpcivi_profileapipost);
	$bpcivi_webupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','contact_id' => $bpcivi_editprofcid,);

	for ($j=0;$j<count($bpcivi_profileapipost);$j++) {
		if (startsWith( $bpcivi_profileapikeys[$j], "url-")) {
			$bpcivi_webnumb = str_replace("url-","",$bpcivi_profileapikeys[$j]);
			$bpcivi_webnumb = str_replace("-urlid","",$bpcivi_webnumb);
		}
		$bpcivi_webnumbmax = $bpcivi_webnumb;
	for ($i=1;$i<($bpcivi_webnumbmax+1);$i++) {
		$bpcivi_idbuild = "url-" . $i . "-urlid";
		$bpcivi_urlbuild = "url-" . $i;
		if(is_numeric ($bpcivi_profileapipost[$bpcivi_idbuild])) {
		  $bpcivi_webarraybuild = array('id' => $bpcivi_profileapipost[$bpcivi_idbuild],'url' => $bpcivi_profileapipost[$bpcivi_urlbuild],'website_type_id' => $i,);
		   $bpcivi_webarrayparamf = array_merge($bpcivi_webupdateparams,$bpcivi_webarraybuild);
		  //Update
		   $bpcivi_weppostresult = civicrm_api('Website', 'update', $bpcivi_webarrayparamf);
		} else {
		  $bpcivi_webarraybuild = array('url' => $bpcivi_profileapipost[$bpcivi_urlbuild],'website_type_id' => $i);
		   $bpcivi_webarrayparamf = array_merge($bpcivi_webupdateparams,$bpcivi_webarraybuild);
		  //Insert
		   $bpcivi_weppostresult = civicrm_api('Website', 'create', $bpcivi_webarrayparamf);
		}
	}
	  unset($bpcivi_profileapipost['wponeonce']);
	}
	//Post Normal
	  $bpcivi_profeditparams = array_merge(array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,),array('id' => $bpcivi_editprofcid),$bpcivi_profileapipost);
	  $bpcivi_profileapiresult = civicrm_api('Contact', 'update', $bpcivi_profeditparams);
	//Post Addresses
	  $bpcivi_apiaddressparamssub = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'id' => $bpcivi_addressnum,);
	  $bpcivi_apiaddressparams = array_merge($bpcivi_apiaddressparamssub,$bpcivi_profileapipost);
	  $bpcivi_apiaddressresult = civicrm_api('Address', 'update', $bpcivi_apiaddressparams);  }
//Get The Values of the profile
	$bpcivi_profileparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	  'id' => $bpcivi_edit_profile_id,);
	$bpcivi_profileendsresult = civicrm_api('UFGroup', 'get', $bpcivi_profileparams);
	$bpcivi_profileendsvalues = $bpcivi_profileendsresult['values'][0];
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
//Get Genders
	$bpcivi_genderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','name' => 'gender',);
	$bpcivi_genderresult = civicrm_api('Constant', 'get', $bpcivi_genderparams);
	$bpcivi_genderresultarr = $bpcivi_genderresult['values'];
//Get the Profile Fields
	$bpcivi_profsetparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'options' => array( 'sort' => 'weight',),
		'uf_group_id' => $bpcivi_edit_profile_id,);
	$bpcivi_profsetresult = civicrm_api('UFField', 'get', $bpcivi_profsetparams);
	$bpcivi_profilesetvaluesarr = $bpcivi_profsetresult['values'];
//Get Utilized websites by contact
	$bpcivi_webprofileparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	  'contact_id' => $bpcivi_editprofcid,);
	$bpcivi_webprofileresult = civicrm_api('Website', 'get', $bpcivi_webprofileparams);
//Get the profile data fields
	$bpcivi_profileparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'profile_id' => $bpcivi_edit_profile_id,
		'contact_id' => $bpcivi_editprofcid, );
	$bpcivi_profileresult = civicrm_api('Profile', 'get', $bpcivi_profileparams);
	$bpcivi_profilevaluesarr = $bpcivi_profileresult['values'];
	$bpcivi_profilekeys = array_keys($bpcivi_profileresult['values']);
	$bpcivi_profilevalues = array_values($bpcivi_profileresult['values']);
	//if ends with -1, remove - (TYP. For Addresses)  Currently this may preclude secondary and more addresses
		for ($i=0;$i<count($bpcivi_profilekeys);$i++) {
			if( substr_compare($bpcivi_profilekeys[$i], "-1", -strlen("-1"), strlen("-1")) === 0 && $bpcivi_profilekeys[$i] !== "url-1") {
				$bpcivi_profilesafekeys[$i] = str_replace("-1","",$bpcivi_profilekeys[$i]);
			} else {
				$bpcivi_profilesafekeys[$i] = $bpcivi_profilekeys[$i];
			}
		}
	$bpcivi_profilevaluessafearr = array_combine($bpcivi_profilesafekeys,$bpcivi_profilevalues);
	$bpcivi_urlcheck = 0;
//Output form
	if($bpcivi_profileendsresult['values'][0]['is_active'] == 1) {
	  echo '<div id="bpcivieditform">';
	  echo '<h4>' . $bpcivi_profileendsresult['values'][0]['title'] . '</h4>';
		  echo $bpcivi_profileendsresult['values'][0]['help_pre'] . '';
	  echo '<form action="" method="post"><table border="1">';
	  echo '<input type="hidden" name="wponeonce" value="' . wp_create_nonce('my-nonce') . '">';
	for ($i=0;$i<count($bpcivi_profilesetvaluesarr);$i++) {
	  if (	$bpcivi_profilesetvaluesarr[$i]['is_active'] == 1) {
		if ( strlen($bpcivi_profilesetvaluesarr[$i]['help_pre'])>1) {
		echo '<tr><td colspan="2"><div id="bpcivi_preform">' . $bpcivi_profilesetvaluesarr[$i]['help_pre'] . '</div></td></tr>';
		}
		echo '<tr><td>';
		  echo $bpcivi_profilesetvaluesarr[$i]['label'];
		echo "</td><td>";
		//If Loop
		if ($bpcivi_profilesetvaluesarr[$i]['field_name'] == "state_province") {
			echo '<select name="' . "state_province_id" . '">';
			for ($j = 0; $j<(count($bpcivi_statesresultarr));$j++) {
			  if ($bpcivi_statesresultarrkeys[$j] == $bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] ) {
				echo '<option value="' . $bpcivi_statesresultarrkeys[$j] . '" selected>' . $bpcivi_statesresultarrkeyd[$j] . "</option>";
			  } else {
				echo '<option value="' . $bpcivi_statesresultarrkeys[$j] . '" >' . $bpcivi_statesresultarrkeyd[$j] . "</option>";
			  } }
			echo '</select>';
		} elseif ($bpcivi_profilesetvaluesarr[$i]['field_name'] == "country") {
			echo '<select name="' . "country_id" . '">';
			for ($j = 0; $j<(count($bpcivi_countriesresultarr));$j++) {
			  if ($bpcivi_countriesresultarrkeys[$j] == $bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] ) {
				echo '<option value="' . $bpcivi_countriesresultarrkeys[$j] . '" selected>' . $bpcivi_countriesresultarrkeyd[$j] . "</option>";
			  } else {
				echo '<option value="' . $bpcivi_countriesresultarrkeys[$j] . '" >' . $bpcivi_countriesresultarrkeyd[$j] . "</option>";
			  }
			}
			echo '</select>';
		} elseif ($bpcivi_profilesetvaluesarr[$i]['field_name'] == "gender") {
			for ($j = 1; $j<(count($bpcivi_genderresultarr)+1);$j++) {
				if ($bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] == $j) {
				  echo '<input type="radio" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $j . '" checked>' . $bpcivi_genderresultarr[$j] . '<br>';
				} else {
				  echo '<input type="radio" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $j . '">' . $bpcivi_genderresultarr[$j] . '<br>';
				} }
		} elseif (startsWith($bpcivi_profilesetvaluesarr[$i]['field_name'], "url-")) {
			$bpcivi_formurl = $bpcivi_webprofileresult['values'][$bpcivi_urlcheck]['url'];
			$bpcivi_formurlid = $bpcivi_webprofileresult['values'][$bpcivi_urlcheck]['id'];
				//bpcivi_formurltypeid = $bpcivi_webprofileresult['values'][$bpcivi_urlcheck]['website_type_id'];
			echo '<input type="url" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_formurl . '">';
			echo '<input type="hidden" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '-urlid' . '" value="' . $bpcivi_formurlid . '">';
			//TODO: Website type here; each website will be listed, but url-1 will be mapped to the first url, etc and may not fall in order
			$bpcivi_urlcheck = $bpcivi_urlcheck + 1;
		} elseif ( checkDate2($bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']]) ) {
			list($mm,$dd,$yy)=explode("/",$bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']]);
			$bpcivi_datecombine = $yy . "-" . $mm . "-" . $dd;
			echo '<input type="date" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_datecombine . '">';
		} elseif ($bpcivi_profilesetvaluesarr[$i]['location_type_id'] == 1) { //Options for a street address
			echo '<input type="text" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] . '">';
		} elseif (startsWith($bpcivi_profilesetvaluesarr[$i]['field_name'], "custom_")) { //Options for a Custom Field Type
			  if ($bpcivi_customfieldresult['values'][str_replace("custom_","",$bpcivi_profilesetvaluesarr[$i]['field_name'])]['data_type'] == "Date") {
				if (strlen($bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']]) < 3) {
					$bpcivi_emptydateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','entity_id' => $bpcivi_editprofcid,);
					$bpcivi_emptydateresult = civicrm_api('CustomValue', 'get', $bpcivi_emptydateparams);
					$bpcivi_emptydateresultfix = $bpcivi_emptydateresult['values'][str_replace("custom_","",$bpcivi_profilesetvaluesarr[$i]['field_name'])]["0"];
					$bpcivi_date = strtotime($bpcivi_emptydateresultfix);
					$bpcivi_date = getdate($bpcivi_date);
					if ($bpcivi_date['mon'] < 10) {
						$bpcivi_safemonth = "0" . $bpcivi_date['mon'];
					  } else { $bpcivi_safemonth = $bpcivi_date['mon']; }
					if ($bpcivi_date['mday'] < 10) {
						$bpcivi_safeday = "0" . $bpcivi_date['mday'];
					  } else { $bpcivi_safeday = $bpcivi_date['mday']; }
					$bpcivi_dateout = $bpcivi_date['year'] . '-' . $bpcivi_safemonth . '-' . $bpcivi_safeday;
					echo '<input type="date" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_dateout . '">'; 
				} else {
					list($mm,$dd,$yy)=explode("/",$bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']]);
					$bpcivi_datecombine = $yy . "-" . $mm . "-" . $dd;
					echo '<input type="date" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_datecombine . '">'; 
				}
			  } elseif ($bpcivi_customfieldresult['values'][str_replace("custom_","",$bpcivi_profilesetvaluesarr[$i]['field_name'])]['data_type'] == "String") {
				echo '<input type="text" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] . '">';
			  }
	//TODO: Here additional custom fields types can be inserted; currently only string and date are supported
		} else {
			echo '<input type="text" name="' . $bpcivi_profilesetvaluesarr[$i]['field_name'] . '" value="' . $bpcivi_profilevaluessafearr[$bpcivi_profilesetvaluesarr[$i]['field_name']] . '">';
		}
		echo '</td></tr>';
	  }
		if ( strlen($bpcivi_profilesetvaluesarr[$i]['help_post'])>1 ) {
		   echo '<tr><td colspan="2"><div id="bpcivi_postform">' . $bpcivi_profilesetvaluesarr[$i]['help_post'] . '</div></td></tr>';
		}
	}
	  echo '</table>';

	echo $bpcivi_profileendsresult['values'][0]['help_post'] . '<br>';
	  echo '<input type="submit">';
	echo '</form>';
	echo '</div>';
	}
//Diagnostics
	//echo "<pre>";
	//print_r($bpcivi_profilesetvaluesarr);
	//echo "</pre>";
	//echo "<pre>";
	//print_r($bpcivi_weppostresult);
	//echo "</pre>";

}
?>
