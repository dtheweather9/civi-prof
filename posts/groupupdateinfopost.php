<?php
//Include Files	
	$civiprof_baseurl = str_replace("/wp-content/plugins/civi-prof/posts","", getcwd());
	include_once($civiprof_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	//WP NONCE check
	
	if(wp_verify_nonce( $_POST['_civiprofgroupupdate'], "civiprofgroupupdate") == false) { 
			 print 'Sorry, your nonce did not verify. Reload.';
   			exit;
	} else {
	
		
	//Finish
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	
	
	if (isset($_POST['groupeditsubmit'])) {
		//Contact Update
		$bpcivi_groupupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'id' => $_POST['orgid'],
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
		'contact_id' => $_POST['orgid'],
		);
		$bpcivi_groupaddresseditpostresult = civicrm_api('Address', 'create', $bpcivi_groupaddressupdateparams);
		//Website Update
		$bpcivi_groupwebupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'id' => $_POST['webid'],
		'url' => $_POST['website1'],
		'contact_id' => $_POST['orgid'],
		);
		$bpcivi_groupwebupdateresult = civicrm_api('Website', 'create', $bpcivi_groupwebupdateparams);
		
		//Diagnostics
	//	echo "bpcivi_groupwebupdateresult<pre>";
	//	print_r($bpcivi_groupwebupdateresult);
	//	echo "<pre>";
		echo '<meta http-equiv="refresh" content="0;URL= '. $_POST['infoupdateredirect_url'] . '">';
  	  }
	}