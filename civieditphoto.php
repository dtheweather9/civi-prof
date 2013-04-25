<?php

add_action('bp_init', 'bpcivi_addphotonav');

function bpcivi_addphotonav() {
//Add Array
  global $bp;
	$bpcivi_photonavparent_url = trailingslashit( $bp->displayed_user->domain . 'profile' );
	$bpcivi_photonavdefaults = array(
		'name'            => 'Member Image', // Display name for the nav item
		'slug'            => 'photo', // URL slug for the nav item
		'parent_slug'     => 'profile', // URL slug of the parent nav item
		'parent_url'      => $bpcivi_photonavparent_url, // URL of the parent item
		'item_css_id'     => bpcivi_imagecss, // The CSS ID to apply to the HTML of the nav item
		'user_has_access' => true,  // Can the logged in user see this nav item?
		'position'        => 90,    // Index of where this nav item should be positioned
		'screen_function' => bpcivi_image_page, // The name of the function to run when clicked
	);

bp_core_new_subnav_item($bpcivi_photonavdefaults);

add_action('bp_template_content', 'bpcivi_photonavpage');

}

function bpcivi_image_page() {
		bp_core_load_template( 'members/single/plugins' );
		}

function bpcivi_photonavpage() {
/*
//Post Section

//Include statements
	include_once(ABSPATH ."/wp-content/plugins/civicrm/civicrm.settings.php");
	include_once(ABSPATH ."/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php");
	include_once(ABSPATH ."/wp-content/plugins/civicrm/civicrm/api/api.php");

//Run look for uploading file
if (isset($_FILES['file']['name'])) {
	$bpcivi_allowedExts = array("jpg", "jpeg", "gif", "png","JPG","JPEG","GIF", "PNG");
	$bpcivi_extension = end(explode(".", $_FILES['file']['name']));
	$bppostfile_location_cwd = getcwd();
	$bppostfile_location = str_replace("/wp-content/plugins/civi-prof","/wp-content/plugins/files/civicrm/custom/", $bppostfile_location_cwd);
	if ((($_FILES['file']['type'] == "image/gif")
	|| ($_FILES['file']['type'] == "image/jpeg")
	|| ($_FILES['file']['type'] == "image/png")
	|| ($_FILES['file']['type'] == "image/pjpeg"))
	&& ($_FILES['file']['size'] < 7000000)
	&& in_array($bpcivi_extension, $bpcivi_allowedExts))
	{
		if ($_FILES['file']['error'] > 0)
	{
	echo "Return Code: " . $_FILES['file']['error'] . "<br>";
	} else {
	//echo "Upload: " . $_FILES['file']['name'] . "<br>";
	//echo "Type: " . $_FILES['file']['type'] . "<br>";
	//echo "Size: " . ($_FILES['file']['size'] / 1024) . " kB<br>";
	//echo "Temp file: " . $_FILES['file']['tmp_name'] . "<br>";
	if (file_exists($bppostfile_location . $_FILES['file']['name']))
	{
				$bpcivi_nimageURI = $bppostfile_location . time() . "_" . $_POST['bprof_cid'] . "." . $bpcivi_extension;
				move_uploaded_file($_FILES['file']['tmp_name'],$bpcivi_nimageURI);
				//echo "Stored in: " . $bppostfile_location . $_FILES['file']['name'];
				$bpcivi_nimageURL = $_POST['bprof_url'] . "/wp-content/plugins/files/civicrm/custom/" . time() . "_" . $_POST['bprof_cid'] . "." . $bpcivi_extension;
				$paramsimg = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'id' => $_POST['bprof_cid'],'image_URL' => $bpcivi_nimageURL);
				$bpcivi_imgupdate = civicrm_api('Contact', 'update', $paramsimg);
	} else {
				$bpcivi_nimageURI = $bppostfile_location . $_POST['bprof_cid'] . "." . $bpcivi_extension;
				move_uploaded_file($_FILES['file']['tmp_name'],$bpcivi_nimageURI);
				//echo "Stored in: " . $bppostfile_location . $_FILES['file']['name'];
				$bpcivi_nimageURL = $_POST['bprof_url'] . "/wp-content/plugins/files/civicrm/custom/" . $_POST['bprof_cid'] . "." . $bpcivi_extension;
				$paramsimg = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'id' => $_POST['bprof_cid'],'image_URL' => $bpcivi_nimageURL);
				$bpcivi_imgupdate = civicrm_api('Contact', 'update', $paramsimg);				
			}
		}
	} else	{
	echo "Invalid file";
if ($_FILES['file']['size'] < 7000000) {
//echo " File is too large"; -Replaced thi
}
	}
}
//echo "<p><pre>";
//print_r($_POST);
//echo "</pre>";

// Form Section
echo '<div id="bpcivi_imageedit">';
	echo "here is the photo nav page";
// Function will be into the member change as an item on the profile sub-nav
//Include statements
	include_once(ABSPATH . "/wp-content/plugins/civicrm/civicrm.php");
	include_once(ABSPATH . "/wp-content/plugins/civicrm/civicrm.settings.php");
	include_once(ABSPATH . "/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php");
	include_once(ABSPATH . "/wp-content/plugins/civicrm/civicrm/api/api.php");
//Get Contact ID
	$bpcivi_wpresult = get_current_user_id();
	$params_ciddet = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','uf_id' => $bpcivi_wpresult, 'domain_id' => 1);
	$bpciviapi_result = civicrm_api('UFMatch', 'get', $params_ciddet);
	$bprof_cid = $bpciviapi_result['values'][3]['contact_id']; //Current Civicrm Member ID

//Get Contact ID's image_URL
	$bpciviapi_contactparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,'contact_id' => $bprof_cid,);
	$bpcivi_contacturlarray = civicrm_api('Contact', 'get', $bpciviapi_contactparams);

//If Image URL is less then html length set the image as the blank image
	if (strlen($bpcivi_contacturlarray['values'][0]['image_URL']) < 4) {
		$bpcivi_img = get_site_url()."/wp-content/plugins/buddypress/bp-core/images/mystery-man.jpg";
	} else {
		$bpcivi_img = $bpcivi_contacturlarray['values'][0]['image_URL'];
	}
// Form for the url inside of DIMs
	echo "<br>";
	echo "<div id='bpcivi-editphotoform'>";
		echo "<h2> Membership Photo </h2>";
			echo "<p> Insert an image for your photo ID card.  The photo here is seperate from your avatar image. </p>";
			echo "<p> Note: All images must be less than 7MB, Print Ready, and either a jpg, jpeg, gif, or png.  </p>";
				if ($_FILES['file']['size'] < 7000000) {
					echo " File is too large - Upload another Image";  }
		echo "<img src=" . $bpcivi_img . '" style="width:150px; margin-bottom:10px;">';
			echo '<form action="" method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="bprof_cid" value=' . $bprof_cid . '>';
			echo '<br>';
			echo "Image File:" . '<input type="file" name="file" id="file">';
			echo '<input type="submit">';
		echo "</form>";
	echo "</div>";
echo '</div>';


}
*/
echo "the page is here";

echo '<form action="civiphotopost.php" enctype="multipart/form-data" method="post">';
echo "<p>Photo</p>";
echo '<input name="image" size="30" type="file">';
echo '<input name="upload" type="submit" value="Upload">';
echo '</form>';

}
