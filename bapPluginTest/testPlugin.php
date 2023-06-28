<?php
/*
Plugin Name: BAP Test Plugin
Plugin URI: http://localhost/
Description: BAP plugin for Assign3 
Author: Gustav Hendricks
Version: 0.1
Author URI: http://www.google.com
Last update: 30 May 2023
*/

$BAP_Plugin_version = "0.1"; //current version of db
//========================================================================================
//all the hooks used by our FAQ demo
register_activation_hook(__FILE__,'BAP_test_install');
//register_deactivation_hook( __FILE__, 'WAD_faq_uninstall' );
register_uninstall_hook( __FILE__, 'BAP_test_uninstall' );

add_action('plugins_loaded', 'BAP_update_check');
add_action('plugin_action_links_'.plugin_basename(__FILE__), 'BAP_test_settingslink' );  

add_shortcode('displayfaq', 'BAP_display_Table');
add_shortcode('CRUDfaq', 'BAP_test_CRUD');
add_action('admin_menu', 'BAP_test_menu');

//========================================================================================
//check to see if there is any update required for the database, 
//just in case we updated the plugin without reactivating it
function BAP_update_check() {
	global $BAP_Plugin_version;
	if (get_site_option('BAP_Plugin_version') != $BAP_Plugin_version) BAP_test_install();   
}

//========================================================================================
//hook for the install function - used to create our table for our simple FAQ
function BAP_test_install () {
	global $wpdb;
	global $BAP_Plugin_version;
//-------

			//update the version of the database with the new one
			update_option( "BAP_Plugin_version", $BAP_Plugin_version );
			add_option("BAP_Plugin_version", $BAP_Plugin_version);

} 

//========================================================================================
//clean up and remove any settings than may be left in the wp_options table from our plugin
function BAP_test_uninstall() {
	delete_site_option($BAP_Plugin_version);
	delete_option($BAP_Plugin_version);
}

//========================================================================================
// add the 'settings' label to the plugin menu
// Add settings link on plugin page
function BAP_test_settingslink($links) { 
	//https://developer.wordpress.org/reference/functions/admin_url/
	//refer the function WAD_faq_menu() above in regards to the menu slug 'WADsimplefaq'
	  array_unshift($links, '<a href="'.admin_url('plugins.php?page=BAPTestPlugin').'">Settings</a>'); 
	  return $links; 
	}

//========================================================================================
//add in the FAQ menu entry under the plugins menu.
//notice the menu slug 'WADsimplefaq' - this is used through the FAQ demo in the URL's to identify this page in the WP dashboard
function BAP_test_menu() {
	add_submenu_page( 'plugins.php', 'BAP Test Plugin Settings', 'BAP Test Plugin', 'manage_options', 'BAPTestPlugin', 'BAP_test_CRUD');
}

//what shows  on the page
function BAP_test_CRUD() {
	//--- some basic debugging for information purposes only
	echo '<h3>Contents of the POST data</h3>';
	pr($_POST); //show the contents of the HTTP POST response from a new/edit command from the form
	echo '<h3>Contents of the REQUEST data</h3>';
	pr($_REQUEST);	 //show the contents of any variables made with a GET HTTP request
//--- end of basic debugging  

	echo  '<div id="msg" style="overflow: auto"></div>
		<div class="wrap">
		<h2>BAP tet Plugin <a href="?page=BAPTestPlugin&command=new" class="add-new-h2">Add New</a></h2>
		<div style="clear: both"></div>';
	$currentPluginVer = get_option( "BAP_Plugin_version" );
	echo '<div>current version of plugin is: </div>';
	echo $currentPluginVer;

	// recommend to parse ALL data/variables before using		
	$BAP_plugin_data = $_POST; //our form data from the insert/update

	//current FAQ id for delete/edit commands
	if (isset($_REQUEST['id'])) 
		$BAP_plugin_id = $_REQUEST['id']; 
	else 
		$BAP_plugin_id = '';

	//current CRUD command		
	if (isset($_REQUEST["command"])) 
		$command = $_REQUEST["command"]; 
	else 
		$command = '';

		//execute the respective function based on the command		
		switch ($command) {
			//operations access through the URL	
				case 'view':
					BAP_plugin_view($BAP_plugin_id);
				break;
				
				case 'edit':
					$msg = BAP_plugin_form('update', $BAP_plugin_id); //notice the $faqid passed for the form for an update/edit
				break;
		
				case 'new':
					//notice that no 'id' is passed from 'new' to the form. 
					//WAD_faq_form will use 'null' as the default 'id' - refer to WAD_faq_form for more details
					$msg = BAP_plugin_form('insert');
				break;
				
			//operations performing the various database tasks based on the previous CRUD command
				case 'delete':
					$msg = BAP_plugin_delete($BAP_plugin_id); //remove a faq entry
					$command = '';
				break;
		
				case 'update':
					$msg = BAP_plugin_update($BAP_plugin_data); //update an existing faq
					$command = '';
				break;
		
				case 'insert':	
					$msg = BAP_plugin_insert($BAP_plugin_data); //prepare a blank form for adding a new faq entry
					$command = '';
				break;
			}
//a simple catchall if the command is not found in the switch selector
if (empty($command)) BAP_plugin_list(); //display a list of the faqs if no command issued

//show any information messages	
	if (!empty($msg)) {
      echo '<p><a href="?page=BAPTestPlugin"> back to the FAQ list </a></p> Message: '.$msg;      
	}
	echo '</div>';
		
}

//========================================================================================
//view all the detail for a single FAQ
function BAP_plugin_view($id) {
	global $wpdb;
 
	//https://developer.wordpress.org/reference/classes/wpdb/#protect-queries-against-sql-injection-attacks
	//safer preferred method of passing values to an SQL query this is not a substitute for data validation
	//this method merely reduces the likelyhood of SQL injections
	$qry = $wpdb->prepare("SELECT * FROM wp_wpgmza_maps");
	
	//$qry = $wpdb->prepare("SELECT * FROM WAD_faq WHERE id = %s",array($id)); //alternative using an array
 //pr($qry); //uncomment this line to see the prepared query
	$row = $wpdb->get_row($qry);
	
	//popular unsafe method
	//$row = $wpdb->get_row("SELECT * FROM WAD_faq WHERE id = '$id'");
	echo '<p>';
	echo "map_title:";
	echo '<br/>';
	echo $row->map_title;
	echo '<p>';
	echo "map_width:";
	echo '<br/>';
	echo $row->map_width;
	echo '<p>';
	echo "map_height:";
	echo '<br/>';
	echo $row->map_height;
	echo '<p>';
	echo "map_start_lat:";
	echo '<br/>';
	echo $row->map_start_lat;
	echo '<p>';
	echo "map_start_lng:";
	echo '<br/>';
	echo $row->map_start_lng;
	echo '<p><a href="?page=BAPTestPlugin">&laquo; back to list</p>';
 }

 //========================================================================================
//remove an existing FAQ from the database
function BAP_plugin_delete($id) {
	global $wpdb;
	
 //$wpdb->delete can also be used here instead of a query
 //refer to the WAD_faq_view for details on the prepared query. 
 //$wpdb->prepare can be omitted if the $wpdb->delete version is used
	$results = $wpdb->query($wpdb->prepare("DELETE FROM wp_wpgmza_maps WHERE id=%s",$id));
	if ($results) {
	   $msg = "BAP Plugin entry $id was successfully deleted.";
	}
	return $msg;
 }

 //========================================================================================
//update an existing FAQ in the database
function BAP_plugin_update($data) {
    global $wpdb, $current_user;
	
//add in data validation and error checking here before updating the database!!
    $wpdb->update('bap_property',
		array( 'locationImageAddress' => stripslashes_deep($data['locationImageAddress']),
				'question' => stripslashes_deep($data['question'])),
		array( 'id' => $data['hid']));
    $msg = "Question and answer ".$data['hid']." has been updated";
    return $msg;
}

//========================================================================================
//add a new FAQ to the database
function BAP_plugin_insert($data) {
    global $wpdb, $current_user;

//add in data validation and error checking here before updating the database!!
    $wpdb->insert( 'WAD_faq',
		array( 'locationImageAddress' => stripslashes_deep($data['locationImageAddress']),
				'question' => stripslashes_deep($data['question'])),
		array( '%s', '%s') );
    $msg = "A BAP Plugin entry has been added";
    return $msg;
}

//========================================================================================
//The main dashboard listing with the CRUD links. Take note of the styleing used to align with
//the Wordpress dashboard. Compare with the Wordpress Pages and Posts pages
function BAP_plugin_list() {
	global $wpdb, $current_user;
 
	//prepare the query for retrieving the FAQ's from the database
	$query = "SELECT id, map_title, map_width, map_height, map_start_lat,map_start_lng, status FROM wp_wpgmza_maps ORDER BY answer_date DESC";
	$allfaqs = $wpdb->get_results($query);
 
	//prepare the table and use a default WP style - wp-list-table widefat and manage-column
	echo '<table class="wp-list-table widefat">
		 <thead>
		 <tr>
			 <th scope="col" class="manage-column">id</th>
			 <th scope="col" class="manage-column">map-title</th>
			 <th scope="col" class="manage-column">map_width</th>
			 <th scope="col" class="manage-column">map_height</th>
			 <th scope="col" class="manage-column">map_start_lat</th>
			 <th scope="col" class="manage-column">map_start_lng</th>
		 </tr>
		 </thead>
		 <tbody>';
	 
		 foreach ($allfaqs as $faq) {
			if ($faq->author_id == 0) $faq->author_id = $current_user->ID;
	 
	 //use a WP function to retrieve user information based on the id
			$user_info = get_userdata($faq->author_id);
			
	 //prepare the URL's for some of the CRUD - note again the use of the menu slug to maintain page location between operations	   
			$edit_link = '?page=BAPTestPlugin&id=' . $faq->id . '&command=edit';
			$view_link ='?page=BAPTestPlugin=' . $faq->id . '&command=view';
			$delete_link = '?page=BAPTestPlugin=' . $faq->id . '&command=delete';
	 
	 //use some inbuilt WP CSS to perform the hover effect for the edit/view/delete links	   
			echo '<tr>';
			echo '<td><strong><a href="'.$edit_link.'" title="Edit question">' . $faq->question . '</a></strong>';
			echo '<div class="row-actions">';
			echo '<span class="edit"><a href="'.$edit_link.'" title="Edit this item">Edit</a></span> | ';
			echo '<span class="view"><a href="'.$view_link.'" title="View this item">View</a></span> | ';
			echo '<span class="trash"><a href="'.$delete_link.'" title="Move this item to Trash" onclick="return doDelete();">Trash</a></span>';
			echo '</div>';
			echo '</td>';
			echo '<td>' . $faq->answer_date . '</td>';
			echo '<td>' . $user_info->user_login . '</td>';
			
	 //display the status in words depending on the current status value in the database - 0 or 1	   
			$status = array('Draft', 'Published');
			 echo '<td>' . $status[$faq->status] . '</td></tr>';  
		 }
		echo '</tbody></table>';
		 
	 //small piece of javascript for the delete confirmation	
		 echo "<script type='text/javascript'>
				 function doDelete() { if (!confirm('Are you sure?')) return false; }
			   </script>";
	 }
	 
	 //========================================================================================
	 //this is the form used for the insert as well as the edit/update of the FAQ data
	 //here we introduce default values for the function parameter list. if the second parameter 
	 //was omitted then the id will assume the value null (insert a new record - has no initial ID)
	 function BAP_plugin_form($command, $id = null) {
		 global $wpdb;
	 
	 //if the current command was 'edit' then retrieve the FAQ record based on the id pased to this function
	 //!!this SQL querey is open to potential injection attacks
		 if ($command == 'update') {
			 $faq = $wpdb->get_row("SELECT * FROM wp_wpgmza_maps");
		 }
	 
	 //if the current command is insert then clear the form variables to ensure we have a blank
	 //form before starting	
		 if(empty($faq)) { // This happens for 'new' also if get_row fails
			 $faq = (object) array('question' => '', 'answer' => '', 'status' => 0);
		 }
	 
	 //prepare the draft/published status for the HTML check boxes	
		 if (isset($faq)) {
			 $draftstatus = ($faq->status == 0)?"checked":"";
			 $pubstatus   = ($faq->status == 1)?"checked":"";
		 }
		 
	 //prepare the HTML form	
		 echo '<form name="WADform" method="post" action="?page=BAPTestPlugin">
			 <input type="hidden" name="hid" value="'.$id.'"/>
			 <input type="hidden" name="command" value="'.$command.'"/>
	 
			 <p>map_title:<br/>
			 <input type="text" name="map_title" value="'.$faq->map_title.'" size="20" class="large-text"/>
			 <p>Answer:<br/>
			 <textarea name="map_width" rows="10" cols="30" class="large-text">'.$faq->map_width.'</textarea>
			 </p><hr />
			 <p>map_height:<br/>
			 <input type="text" name="map_height" value="'.$faq->map_height.'" size="20" class="large-text"/>
			 <p>Answer:<br/>
			 <textarea name="map_start_lat" rows="10" cols="30" class="large-text">'.$faq->map_start_lat.'</textarea>
			 </p><hr />
			 <p>Answer:<br/>
			 <textarea name="map_start_lng" rows="10" cols="30" class="large-text">'.$faq->map_start_lng.'</textarea>
			 </p><hr />
			 <p class="submit"><input type="submit" name="Submit" value="Save Changes" class="button-primary" /></p>
			 </form>';
		echo '<p><a href="?page=WADsimplefaq">&laquo; back to list</p>';		
	 }
	 ?>