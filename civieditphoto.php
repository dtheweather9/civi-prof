<?php

function curPageURLphoto() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function bpcivi_fs_get_wp_config_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base))."/wp-config.php"))
    {
        $path = dirname(dirname($base))."/wp-config.php";
    }
    else
    if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
    {
        $path = dirname(dirname(dirname($base)))."/wp-config.php";
    }
    else
    $path = false;

    if ($path != false)
    {
        $path = str_replace("\\", "/", $path);
    }
    $path = str_replace("wp-config.php","",$path);   
    return $path;
}

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
	bp_core_load_template( 'members/single/plugins' ); //Loads general members/single/plugins template
}

function bpcivi_photonavpage() {
	//Run the civicrm loading sequence, intial values
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
		$config = CRM_Core_Config::singleton();
	//Control Settings, Location, and Variable
		$bpcivi_filelocation = 'wp-content/plugins/files/civicrm/custom/';
		$bpcivi_filetime = microtime();
	//Get Civicrm Contact ID
		$bpcivi_edituserparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		  'uf_id' => get_current_user_id(),);
		$bpcivi_edituserresult = civicrm_api('UFMatch', 'get', $bpcivi_edituserparams);
		$bpcivi_editphotocid = $bpcivi_edituserresult['values'][min(array_keys($bpcivi_edituserresult['values']))]['contact_id'];
	//Get Civicrm Contact info
		$bpcivi_photocontactparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  			'id' => $bpcivi_editphotocid,);
  		$bpcivi_photocontactresult = civicrm_api('Contact', 'getsingle', $bpcivi_photocontactparams);
//Display and Operational Decisions
	if (isset($_POST["upload"])) {  //Go into loop if its a new picture
		$bpcivi_allowedExts = array("jpg", "jpeg", "gif", "png","JPG","JPEG","GIF", "PNG");
		$bpcivi_extension = end(explode(".", $_FILES['image']['name']));
		if ((($_FILES['image']['type'] == "image/gif")
		|| ($_FILES['image']['type'] == "image/jpeg")
			|| ($_FILES['image']['type'] == "image/png")
			|| ($_FILES['image']['type'] == "image/pjpeg"))
			&& ($_FILES['image']['size'] < 7000000) //Less than 7MB
			&& in_array($bpcivi_extension, $bpcivi_allowedExts))	{
				if ($_FILES['image']['error'] > 0) {
					echo "Return Code: " . $_FILES['image']['error'] . "<br>";
				} else {
				//No Errors and file is valid file
					//File location
						$bpcivi_img = get_site_url()."/wp-content/plugins/buddypress/bp-core/images/mystery-man.jpg";
					//Load in Javascript and CSS for image cropping
					echo '<link rel="stylesheet" type="text/css" href="' . get_site_url() . '/wp-content/plugins/civi-prof/css/imgareaselect-default.css" />';
					echo '<script type="text/javascript" src="' . get_site_url() . '/wp-content/plugins/civi-prof/scripts/jquery.min.js"></script>';
					echo '<script type="text/javascript" src="' . get_site_url() . '/wp-content/plugins/civi-prof/scripts/jquery.imgareaselect.pack.js"></script>';
					//Set function for preview window
					echo '<script type="text/javascript">';
					echo '</script>';
//Print Image
echo '<script type="text/javascript">';
print <<<EJEND

$(document).ready(function () {
	$('#tempimg').imgAreaSelect({
		onSelectEnd: function (img, selection) {
			$('input[name="x1"]').val(selection.x1);
			$('input[name="y1"]').val(selection.y1);
			$('input[name="x2"]').val(selection.x2);
			$('input[name="y2"]').val(selection.y2);            
		}, aspectRatio: '4:3', handles: true
	});
});

EJEND;
echo '</script>';				
				
		//Move file
		$bpcivi_photoURIa =  bpcivi_fs_get_wp_config_path() . $bpcivi_filelocation . $_FILES['image']['name'];
		move_uploaded_file($_FILES['image']['tmp_name'],$bpcivi_photoURIa);
		//Form
		echo '<form action="" enctype="multipart/form-data" method="post">';
		echo "<p>Photo 2</p>";
		echo '<img id="tempimg" src="' . get_site_url() ."/" . $bpcivi_filelocation . $_FILES['image']['name'] . '" width="400">';
		echo '<input type="hidden" name="siteid" value=' . curPageURLphoto() . '>';
		echo '<input type="hidden" name="x1" value="" />';
		echo '<input type="hidden" name="y1" value="" />';
		echo '<input type="hidden" name="x2" value="" />';
		echo '<input type="hidden" name="y2" value="" />';
		echo '<input type="hidden" name="bpfilename" value="' . $_FILES['image']['name'] . '" />';
		echo '<input name="upload2" type="submit" value="Upload">';
		echo '</form>';
				}
			} else { //End The file is valid if statement
				//Insert something here when the file is not valid
				if($_FILES['image']['size'] > 7000000) {
					echo "<p>The File is too Large</p>";
				} else {
					echo "<p>Incorrect File Type; Please Try Again</p>";
					}
				}//End Valid File loop
		} // End std 'Upload' loop
	if (isset($_POST["upload2"])) { //Submitting the updated photo and refreshing
		
		$bpcivi_photoURIa =  bpcivi_fs_get_wp_config_path() . $bpcivi_filelocation . $POST['bpfilename'];
		$bpcivi_imagewidth = $_POST['x2'] - $_POST['x1'];
		$bpcivi_imageheight = $_POST['y2'] - $_POST['y1'];
		
		switch( $_FILES['image']['type']) {
			case 'image/png': $bpcivi_img = imagecreatefrompng($file);
			break;
			case 'image/jpeg': $bpcivi_img = imagecreatefromjpeg($file);
			break;
			case 'image/pjpeg': $bpcivi_img = imagecreatefromjpeg($file);
			break;
			case 'image/gif': $bpcivi_img = imagecreatefromgif($file);
			break;
			//default: die();  
			}	
		} // End 'Upload2' loop
		
	//If Image URL is blacnk set the image as the blank image
if (!isset($_POST['upload2']) || !isset($_POST['upload']) ) {
		if (strlen($bpcivi_photocontactresult['image_URL']) < 4) {
			$bpcivi_img = get_site_url()."/wp-content/plugins/buddypress/bp-core/images/mystery-man.jpg";
			   } else {
				$bpcivi_img = $bpcivi_photocontactresult['image_URL'];
			   } 
		//Display the image
		echo '<div id="bpcivi_memberimage">';
		echo '<img src="' . $bpcivi_img . '" width="150">';
		echo "</div>";
		//Display the form
		echo '<form action="" enctype="multipart/form-data" method="post">';
		echo "<p>Photo</p>";
		echo '<input name="image" size="30" type="file">';
		echo '<input type="hidden" name="siteid" value=' . curPageURLphoto() . '>';
		echo '<input name="upload" type="submit" value="Upload">';
		echo '</form>';
	}
//Diagnostics		
		echo "Diagnostics: <br>";
		echo "_FILES: ";
		echo "<pre>";
		print_r($_FILES['image']);
		echo "</pre>";
		echo "_POST: ";
		echo "<pre>";
		print_r($_POST);
		echo "</pre>";
echo "the page is here";
echo "<p>" . $bpcivi_photoURIa . "</p>";
echo "New Image is this: ";
echo '<img src = "' . get_site_url() ."/" . $bpcivi_filelocation . $_FILES['image']['name'] . '">';

echo "The Single Contact: ";
echo "<pre>";
print_r($bpcivi_photocontactresult['image_URL']);
echo "</pre>";
}
