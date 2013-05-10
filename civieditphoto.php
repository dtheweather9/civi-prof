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
		'slug'            => 'membphoto', // URL slug for the nav item
		'parent_slug'     => 'profile', // URL slug of the parent nav item
		'parent_url'      => $bpcivi_photonavparent_url, // URL of the parent item
		'item_css_id'     => bpcivi_imagecss, // The CSS ID to apply to the HTML of the nav item
		'user_has_access' => true,  // Can the logged in user see this nav item?
		'position'        => 90,    // Index of where this nav item should be positioned
		'screen_function' => bpcivi_image_page, // The name of the function to run when clicked
	);

bp_core_new_subnav_item($bpcivi_photonavdefaults);
add_action('bp_template_content', 'bpcivi_image_page_content');
}

function bpcivi_image_page() {
	bp_core_load_template( 'members/single/plugins' ); //Loads general members/single/plugins template
}

function bpcivi_image_page_content() {
	global $bp;
	if ($bp->current_action == 'membphoto' ) {  //If the Action 
		bpcivi_photonavpage();  
	}
}

function bpcivi_photonavpage() {
	//Run the civicrm loading sequence, intial values
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
		$config = CRM_Core_Config::singleton();
	//Control Settings, Location, and Variable
		$bpcivi_filelocation = 'wp-content/plugins/files/civicrm/custom/';
		$bpcivi_memimgsize = 400;
	//Get Civicrm Contact ID
		$bpcivi_edituserparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		  'uf_id' => get_current_user_id(),);
		$bpcivi_edituserresult = civicrm_api('UFMatch', 'get', $bpcivi_edituserparams);
		$bpcivi_editphotocid = $bpcivi_edituserresult['values'][min(array_keys($bpcivi_edituserresult['values']))]['contact_id'];
	//Get Civicrm Contact info
		$bpcivi_photocontactparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  			'id' => $bpcivi_editphotocid,);
  		$bpcivi_photocontactresult = civicrm_api('Contact', 'getsingle', $bpcivi_photocontactparams);
  		$bpcivi_filename = $bpcivi_editphotocid;
  		if (strlen($bpcivi_filename) < 1) {
  			$bpcivi_filename = rand(900000,999999);
  		}
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
						//TODO: Add Preview Window
					echo '</script>';
		//Print Image
		$bpcivi_script1=file_get_contents(__DIR__ . "/scripts/inlineimg.html");
		echo $bpcivi_script1;
		//Move file
		$bpcivi_photoURIa =  bpcivi_fs_get_wp_config_path() . $bpcivi_filelocation . str_replace(" ", "",$_FILES['image']['name']);
		echo move_uploaded_file($_FILES['image']['tmp_name'],$bpcivi_photoURIa);
		//Form
		echo '<form action="" enctype="multipart/form-data" method="post">';
		echo "<p><h3>Crop Image</h3></p>";
		echo "<p>Select the Area on your image which you would like to use for your membership card image</p>";
		echo '<img id="tempimg" src="' . get_site_url() ."/" . $bpcivi_filelocation . str_replace(" ", "",$_FILES['image']['name']) . '" width="' . $bpcivi_memimgsize . '">';
		echo '<input type="hidden" name="siteid" value=' . curPageURLphoto() . '>';
		echo '<input type="hidden" name="x1" value="" />';
		echo '<input type="hidden" name="y1" value="" />';
		echo '<input type="hidden" name="x2" value="" />';
		echo '<input type="hidden" name="y2" value="" />';
		echo '<input type="hidden" name="bpfilename" value="' . str_replace(" ", "",$_FILES['image']['name']) . '" />';
		echo '<input type="hidden" name="bpfiletype" value="' . $_FILES['image']['type'] . '" />';
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
		$bpcivi_photofile = get_site_url() . "/" . $bpcivi_filelocation . $_POST['bpfilename'];
		switch( $_POST['bpfiletype']) {
			case 'image/png': $bpcivi_img = imagecreatefrompng($bpcivi_photofile);
			break;
			case 'image/jpeg': $bpcivi_img = imagecreatefromjpeg($bpcivi_photofile);
			break;
			case 'image/pjpeg': $bpcivi_img = imagecreatefromjpeg($bpcivi_photofile);
			break;
			case 'image/gif': $bpcivi_img = imagecreatefromgif($bpcivi_photofile);
			break;
			//default: die();  
			}
		switch( $_POST['bpfiletype']) {
			case 'image/png': $bpcivi_fileend = ".png";
			break;
			case 'image/jpeg': $bpcivi_fileend = ".jpeg";
			break;
			case 'image/pjpeg': $bpcivi_fileend = ".jpeg";
			break;
			case 'image/gif': $bpcivi_fileend = ".gif";
			break;
			//default: die();  
			}
			$bpcivi_cropcanvas = imagecreatetruecolor($bpcivi_imagewidth,$bpcivi_imageheight);
			list($bpcivi_currentwidth, $bpcivi_currentheight) = getimagesize($bpcivi_photofile);
			//Rescale X and Y when the image is a different width
				$bpcivi_newimgX = $bpcivi_currentwidth/$bpcivi_memimgsize*$_POST['x1'];
				$bpcivi_newimgY = $bpcivi_currentwidth/$bpcivi_memimgsize*$_POST['y1'];
			//Rescale the image
			imagecopyresampled($bpcivi_cropcanvas,$bpcivi_img, 0,0,$bpcivi_newimgX, $bpcivi_newimgY, $bpcivi_imagewidth, $bpcivi_imageheight,$bpcivi_currentwidth/$bpcivi_memimgsize*$bpcivi_imagewidth, $bpcivi_currentwidth/$bpcivi_memimgsize*$bpcivi_imageheight);
			$bpcivi_photofileend = $bpcivi_photoURIa . $bpcivi_filename . $bpcivi_fileend;
			$bpcivi_photourlend = get_site_url() ."/" . $bpcivi_filelocation . $bpcivi_filename . $bpcivi_fileend;
		switch( $_POST['bpfiletype']) {
			case 'image/png': imagepng($bpcivi_cropcanvas, $bpcivi_photofileend, 5);
			break;
			case 'image/jpeg': imagejpeg($bpcivi_cropcanvas, $bpcivi_photofileend, 100);
			break;
			case 'image/pjpeg': imagejpeg($bpcivi_cropcanvas, $bpcivi_photofileend, 100);
			break;
			case 'image/gif': imagegif($bpcivi_cropcanvas, $bpcivi_photofileend);
			break;
			//default: die();  
			}
			//Write new file as Civicrm URL
			$bpcivi_photoupdateparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'id' => $bpcivi_editphotocid,'image_URL' => $bpcivi_photourlend);
			$bpcivi_imgupdate = civicrm_api('Contact', 'update', $bpcivi_photoupdateparams);				
		
		echo '<p><h3>Your Membership Photo has been updated:</h3></p><img src = "' . $bpcivi_photourlend . '">';
		} // End 'Upload2' loop
		
//If Image URL is blank set the image as the blank image
	if (!(isset($_POST['upload2']) || isset($_POST['upload']) )) {
		if (strlen($bpcivi_photocontactresult['image_URL']) < 4) {
			$bpcivi_img = get_site_url()."/wp-content/plugins/buddypress/bp-core/images/mystery-man.jpg";
			   } else {
				$bpcivi_img = $bpcivi_photocontactresult['image_URL'];
			   } 
		//Display the image
		echo '<div id="bpcivi_memberimage">';
		echo "<p><h3>Your Current Membership Photo: </h3></p>";
		echo '<img src="' . $bpcivi_img . '" width="150">';
		echo "</div><p></p>";
		//Display the form
		echo "<p>You can upload up a 7 MB File to the system of either JPEG, PNG, or GIF format.  You will be able to crop the image in this menu.</p>";
		echo '<form action="" enctype="multipart/form-data" method="post">';
		echo '<input name="image" size="30" type="file">';
		echo '<input type="hidden" name="siteid" value=' . curPageURLphoto() . '>';
		echo "Upload New Photo: " .'<input name="upload" type="submit" value="Upload">';
		echo '</form>';
		
	}
//Diagnostics	
/*	
		//Load in CSS for diagnostics
			echo '<link rel="stylesheet" type="text/css" href="' . get_site_url() . '/wp-content/plugins/civi-prof/css/bpcivigeneral.css" />';
		//Continue Dignostics
		echo '<div id="bpcivi_diag">';
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
	echo "URI Location for Image: " . $bpcivi_photoURIa;
	echo "</div>";
*/	
}
