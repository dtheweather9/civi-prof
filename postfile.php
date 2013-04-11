<?php
//Get Root Location
$bppostfile_rLoc_cwd = getcwd();
$bppostfile_rLoc = str_replace("/wp-content/plugins/civi-prof","/", $bppostfile_rLoc_cwd);
//Include statements
  include_once($bppostfile_rLoc."/wp-content/plugins/civicrm/civicrm.settings.php");
	include_once($bppostfile_rLoc."/wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php");
	include_once($bppostfile_rLoc."/wp-content/plugins/civicrm/civicrm/api/api.php");

//Run look for uploading file
if (isset($_FILES['file']['name'])) {
	$bpcivi_allowedExts = array("jpg", "jpeg", "gif", "png","JPG","JPEG","GIF", "PNG");
	$bpcivi_extension = end(explode(".", $_FILES['file']['name']));
	$bppostfile_location_cwd = getcwd();
	$bppostfile_location = str_replace("/wp-content/plugins/bp-civiprof","/wp-content/plugins/files/civicrm/custom/", $bppostfile_location_cwd);
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
echo " File is too large";
}
	}
}
//echo "<p><pre>";
//print_r($_POST);
//echo "</pre>";

//Return to initial page (passed from form through post)
echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $_POST['hvurls'] . '">';
?>
