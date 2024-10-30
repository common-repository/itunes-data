<?php
/**
 * iTunes Data
 *
 * Displays data from an uploaded iTunes XML file
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    iTunes-Data
 * @version 1.0
 * @filesource
 * @copyright 2009 Eric Lamb.
 */
/*
Plugin Name: iTunes-Data
Plugin URI: http://blog.ericlamb.net/itunes-data/
Description: Displays data from an uploaded iTunes XML file.
Version: 1.0
Author: Eric Lamb
Author URI: http://blog.ericlamb.net
*/

/*  Copyright 2009  Eric Lamb  (email : eric@ericlamb.net)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Version of iTunes-Data code
 * @global string	$itunes_data_version 
 */
$itunes_data_version = "1.0";

/**
 * Version of iTunes-Data database
 * @global string	$itunes_data_db_version 
 */
$itunes_data_db_version = "1.0";

$wpdb->itunes_data_table_name = $wpdb->prefix . "itunes";

/**
 * Displays the actual sidebar data
 *
 * @return  void
 */
function itunes_data_sidebar()
{
	$val = rand(0,4);
	$options = get_option('itunes_data');
	$total = $options['num_display'];
	switch($val){

		case '0':
			$result = itunes_data_get_recent_additions($total);
			$count = count($result);
			echo '<div align="right" class="itunes_type_header">Recently Added</div>';
			echo '<ul>';
			for($i=0;$i<$count;$i++){
				$Album = ($result[$i]->ipod_track_album != '' ? $result[$i]->ipod_track_album : 'N/A');
				echo '<li><a href="javascript:;" title="Artist: '.$result[$i]->ipod_track_artist.' :: Album:  '.$Album.' :: Date: '.itunes_data_relative_time(strtotime($result[$i]->ipod_track_date_added)).'">'.$result[$i]->ipod_track_artist.'</a></li>';
			}
			echo '</ul>';
		break;

		case '1':
			$result = itunes_data_get_top_artists($total);
			$count = count($result);
			echo '<div align="right" class="itunes_type_header">Top Artists</div>';
			echo '<ul>';
			for($i=0;$i<$count;$i++){
				echo '<li><a href="javascript:;" title="Artist: '.$result[$i]->ipod_track_artist.' :: Listened to: '.number_format_i18n($result[$i]->ipod_track_play_count).' times">'.$result[$i]->ipod_track_artist.'</a></li>';
			}
			echo '</ul>';
		break;

		case '2':

			$result = itunes_data_get_top_albums($total);
			$count = count($result);
			echo '<div align="right" class="itunes_type_header">Top Albums</div>';
			echo '<ul>';
			for($i=0;$i<$count;$i++){
				echo '<li><a href="javascript:;" title="Artist: '.$result[$i]->ipod_track_artist.' :: Album:'.$result[$i]->ipod_track_album.'">'.$result[$i]->ipod_track_album.'</a></li>';
			}
			echo '</ul>';
		break;

		case '3':
			$result = itunes_data_get_top_songs($total);
			$count = count($result);
			echo '<div align="right" class="itunes_type_header">Top Songs</div>';
			echo '<ul>';
			for($i=0;$i<$count;$i++){
				echo '<li><a href="javascript:;" title="Artist: '.$result[$i]->ipod_track_artist.' :: Track Name: '.$result[$i]->ipod_track_name.' Listened to ::  '.number_format_i18n($result[$i]->ipod_track_play_count).' times">'.$result[$i]->ipod_track_name.'</a></li>';
			}
			echo '</ul>';
		break;

		case '4':
		default:
			$result = itunes_data_get_top_genres($total);
			$count = count($result);
			echo '<div align="right" class="itunes_type_header">Top Genres</div>';
			echo '<ul>';
			for($i=0;$i<$count;$i++){
				echo '<li><a href="javascript:;" title="Genre: '.$result[$i]->ipod_track_genre.' :: '.number_format_i18n($result[$i]->Count).' Songs" >'.$result[$i]->ipod_track_genre.' ('.number_format_i18n($result[$i]->Count).')</a></li>';
			}
			echo '</ul>';
		break;

	}
}	

/**
 * Displays the actual sidebar header
 *
 * @param   string  $args
 * @return  array
 */
function itunes_data_sidebar_header($args)
{
	echo "\n<!-- iTunes Data Sidebar widget -->\n<link rel=\"stylesheet\" href=\"".get_bloginfo('url')."/wp-content/plugins/".basename(dirname(__FILE__))."/itunes_data_sidebar.css\" type=\"text/css\" media=\"screen\" />\n";
}

/**
 * Inititialize 
 *
 * @param   string  $args	amount of rows to return
 * @return  array
 */
function itunes_data_sidebar_init(){
	global $wpdb;

	if( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	register_sidebar_widget('iTunes Data', 'itunes_data_widget_sidebar');
	register_widget_control('iTunes Data', 'widget_itunes_data_control');
}

function widget_itunes_data_control(){

	$itunes_num_display = (int)get_option('itunes_num_display');
	if(isset($_POST['itunes_num_display'])){
		$options = get_option('itunes_data');
		$itunes_num_display = (isset($_POST['itunes_num_display']) ? (int)$_POST['itunes_num_display'] : (int)$options['num_display']);
		$options['num_display'] = $itunes_num_display;
		update_option('itunes_data', $options);
	}
	echo '<p><label for="itunes_num_display"># To Display: <input class="widefat" id="itunes_num_display" name="itunes_num_display" value="'.$itunes_num_display.'" type="text" style="width:60px"></label></p>';
}

function itunes_data_admin_page() {
	global $itunes_data_db_version,$wpdb,$itunes_data_page_total;

	$new_version = TRUE;
	$page_errors = array();
	$page_msg = array();

	$plugin_data = get_plugin_data(__FILE__);
	$current = get_option('update_plugins');
	$options = get_option('itunes_data');
	$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : FALSE);
	$file = plugin_basename(__FILE__);

	if(isset($_POST['goform']) && $_POST['goform'] == 'yes'){//form submission
		
		if (is_uploaded_file($_FILES['itunes_xml']['tmp_name'])) {

			$uploadfile = dirname(__FILE__).'/tmp/'.$_FILES['itunes_xml']['name'];
			$working_dir = dirname(__FILE__).'/tmp/';
			if (move_uploaded_file($_FILES['itunes_xml']['tmp_name'], $uploadfile)) {

				$file_ext = substr($_FILES['itunes_xml']['name'],-3);
				$upload_file = $_FILES['itunes_xml']['tmp_name'];

				switch($file_ext) {
					
					case 'zip':

						require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
						$archive = new PclZip($uploadfile);
						if (FALSE == ($zip_data = $archive->extract($working_dir))) {
							$page_errors[] = "Error : ".$archive->errorInfo(true);
						}

						$total_files = count($zip_data);
						if($total_files != '1'){
							$page_errors[] = "Error : More than one file in the archive; can't determine iTunes";
						} else {
							$zip_data = $zip_data['0'];
							$file_ext = substr($zip_data['filename'],-3);
							@unlink($uploadfile);
							$uploadfile = $zip_data['filename'];
						}

					break;
				}

				if(count($page_errors) == '0'){

					if($file_ext == 'xml'){

						if(class_exists('DomDocument')){
							include "itunes_xml_parser_php5.php";
						} else {
							include "itunes_xml_parser.php";
						}
						$songs = iTunesXmlParser($uploadfile);

						if ($songs) {
							$sql = "TRUNCATE ".$wpdb->prefix . "itunes";;
							$wpdb->query($sql);

							$total_xml = count($songs);
							if($total_xml >= '1'){
								$total_added = 0;
								$options['last_uploaded'] = mktime();
								update_option('itunes_data', $options);
								foreach ($songs as $song) {
									
									if(itunes_data_add_song($song)){
										$total_added++;
									}
								}
							}
							$page_msg[] = "Success : imported ".number_format_i18n($total_added)." of ".number_format_i18n($total_xml)." songs in the XML.";

						} else {
							$page_errors[] = "Error : Couldn't extract songs from XML.";
						}
						
					} else {
						$page_errors[] = "Error : Can't extract songs from file. Are you sure it's a valid iTunes XML file? ";
					}
				}
				if(isset($uploadfile)){
					if(is_file($uploadfile)){
						@unlink($uploadfile);
					}
				}
			}
		}

		if(count($page_errors) == '0' && $options['num_display'] != $_POST['itunes_num_display']){
			$itunes_num_display = (isset($_POST['itunes_num_display']) ? (int)$_POST['itunes_num_display'] : (int)$options['num_display']);
			$options['num_display'] = $itunes_num_display;
			$options['last_modified'] = mktime();
			update_option('itunes_data', $options);
			$page_msg[] = "Settings successfully changed";
		}
	}



	?>
<div class="wrap" id="sm_div">
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $page; ?>" enctype="multipart/form-data">
	<div id="icon-upload" class="icon32"><br /></div>
		<h2><?php echo $plugin_data['Name']; ?> <?php echo $plugin_data['Version']; ?></h2>
	<?

	if(!is_writable(dirname(__FILE__).'/tmp/')){
		itunes_data_admin_message( __('Can not write to tmp directory. Make sure '.dirname(__FILE__).'/tmp/ is writable.','default'));
	}

	//check if an update has been issued
	if(isset($current->response[$file])) {
		$r = $current->response[$file];
		if ( !current_user_can('edit_plugins') || version_compare($wp_version,"2.5","<") ) {
			itunes_data_admin_message( __('There is a new version of '.$plugin_data['Name'].' available. <a href="'.$r->url.'">Download version '.$r->new_version.' here</a>.','default'));
		} elseif ( empty($r->package) ) {
			itunes_data_admin_message( __('There is a new version of '.$plugin_data['Name'].' available. <a href="'.$r->url.'">Download version '.$r->new_version.' here</a> <em>automatic upgrade unavailable for this plugin</em>.','default'));
		} else {
			itunes_data_admin_message( __('There is a new version of '.$plugin_data['Name'].' available. <a href="'.$r->url.'">Download version '.$r->new_version.' here</a> or <a href="'.wp_nonce_url("update.php?action=upgrade-plugin&amp;plugin=$file", 'upgrade-plugin_' . $file).'">upgrade automatically</a>.','default'));
		}
	}

	if(count($page_errors) >= '1'){

		foreach($page_errors as $error){
			itunes_data_admin_message($error.' :(');
		}
	}

	if(count($page_msg) >= '1'){

		foreach($page_msg as $msg){
			itunes_data_admin_message($msg.' :)');
		}
	}

	//start the page

	$sql = "SELECT COUNT(ipod_track_id) AS TrackCount, COUNT(DISTINCT(ipod_track_artist)) AS ArtistCount, COUNT(DISTINCT(ipod_track_genre)) AS GenreCount, Min(ipod_track_date_added) AS FirstAdded, Max(ipod_track_date_added) AS LastAdded FROM ".$wpdb->itunes_data_table_name;
	$itunes_info = $wpdb->get_results($sql);

	if($itunes_info['0']->TrackCount == '0'){
		itunes_data_admin_message( __('You haven\'t uploaded an iTunes XML file yet. You should probably do that... :)','default'));
	}


	?>

    <h3>Options</h3>
	<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e("Last Modified:", 'itunes_data' ); ?></th>
            <td><?php echo itunes_data_relative_time($options['last_modified']); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Last Uploaded:", 'itunes_data' ); ?></th>
            <td><?php echo itunes_data_relative_time($options['last_uploaded']); ?></td>
        </tr>
		<tr valign="top">
            <th scope="row"><?php _e("Upload iTunes XML:", 'itunes_data' ); ?></th>
            <td><input type="file" name="itunes_xml" /> Maximum Upload Size: <?php echo ini_get('upload_max_filesize'); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("# To Display:", 'itunes_data' ); ?></th>
            <td><input class="widefat" id="itunes_num_display" name="itunes_num_display" value="<?php echo $options['num_display'];?>" type="text" style="width:60px"></td>
        </tr>
    </table>
	<input type="hidden" name="page" value="<?=$page;?>" />
	<input type="hidden" name="goform" value="yes" />
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>"/>
    </p>
	</form>

	<h3>General Stats</h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e("Total Songs:", 'itunes_data' ); ?></th>
            <td><?php echo number_format_i18n($itunes_info['0']->TrackCount); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Total Artists:", 'itunes_data' ); ?></th>
            <td><?php echo number_format_i18n($itunes_info['0']->ArtistCount); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Total Genres:", 'itunes_data' ); ?></th>
            <td><?php echo number_format_i18n($itunes_info['0']->GenreCount); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("First Added:", 'itunes_data' ); ?></th>
            <td><?php echo itunes_data_relative_time(strtotime($itunes_info['0']->FirstAdded)); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Last Added:", 'itunes_data' ); ?></th>
            <td><?php echo itunes_data_relative_time(strtotime($itunes_info['0']->LastAdded)); ?></td>
        </tr>
	</table>

	<?
}

/**
 * Displays the message on admin page
 *
 * @return  void
 */
function itunes_data_admin_message($message){
?>
<div class="updated">
	<strong><p><?php echo $message; ?></p></strong>
</div>
<?
}

function itunes_data_widget_sidebar($args)
{
	extract($args);
	echo "<!-- Start iTunes Data widget -->\n";
	echo $before_widget . $before_title . $widget_name . $after_title;
	itunes_data_sidebar();
	echo $after_widget;
	echo "\t<!-- End iTunes Data widget -->\n";
}

function itunes_data_admin()
{
	//add_options_page('iTunes Data', 'iTunes Data', 5, __FILE__, 'itunes_data_admin_page');
	add_submenu_page('themes.php', __('iTunes Data'), __('iTunes Data'), 8, __FILE__, 'itunes_data_admin_page');
}

/**
 * Include install library
 *
 */
include 'install.inc.php';

/**
 * Returns an array of the most played songs
 *
 * @param   int  $limit	amount of rows to return
 * @return  array
 */
function itunes_data_get_top_songs($limit = 10) {
	global $wpdb;

	$sql = "SELECT ipod_track_name,ipod_track_artist,ipod_track_play_count FROM ".$wpdb->itunes_data_table_name." GROUP BY ipod_track_name ORDER by ipod_track_play_count DESC LIMIT ".$wpdb->escape($limit)."";
	return $wpdb->get_results($sql);
}

/**
 * Returns an array of the most played songs
 *
 * @param   int  $limit	amount of rows to return
 * @return  array
 */
function itunes_data_get_top_artists($limit = 10) {
	global $wpdb;

	$sql = "SELECT DISTINCT(ipod_track_artist),ipod_track_play_count FROM ".$wpdb->itunes_data_table_name." GROUP BY ipod_track_artist ORDER by ipod_track_play_count DESC LIMIT ".$wpdb->escape($limit)."";
	return $wpdb->get_results($sql);
}

/**
 * Returns an array of the recently added songs
 *
 * @param   int  $limit	amount of rows to return
 * @return  array
 */
function itunes_data_get_recent_additions($limit = 10) {
	global $wpdb;

	$sql = "SELECT DISTINCT(ipod_track_artist),ipod_track_date_added,ipod_track_album FROM ".$wpdb->itunes_data_table_name." GROUP BY ipod_track_artist ORDER by ipod_track_date_added DESC LIMIT ".$wpdb->escape($limit)."";
	return $wpdb->get_results($sql);
}

/**
 * Returns an array of the most played albums
 *
 * @param   int  $limit	amount of rows to return
 * @return  array
 */
function itunes_data_get_top_albums($limit = 10) {
	global $wpdb;

	$sql = "SELECT DISTINCT(ipod_track_album),ipod_track_artist FROM ".$wpdb->itunes_data_table_name." ORDER by ipod_track_play_count DESC LIMIT ".$wpdb->escape($limit)."";
	return $wpdb->get_results($sql);
}

/**
 * Returns an array of the genres
 *
 * @param   int  $limit	amount of rows to return
 * @return  array
 */
function itunes_data_get_top_genres($limit = 10){
	global $wpdb;

	$sql = "SELECT DISTINCT(ipod_track_genre),COUNT(ipod_track_genre) AS Count FROM ".$wpdb->itunes_data_table_name." WHERE ipod_track_genre != '' GROUP BY ipod_track_genre ORDER BY Count DESC LIMIT ".$wpdb->escape($limit)."";
	return $wpdb->get_results($sql);
}

/**
 * Adds a new song into the database
 *
 * @param   array  $song	data to add
 * @return  bool
 */
function itunes_data_add_song($song){
	global $wpdb;

	$sql = "INSERT INTO ".$wpdb->prefix . "itunes SET ipod_track_name = '".$wpdb->escape($song["Name"])."', ipod_track_artist = '".$wpdb->escape($song["Artist"])."', ipod_track_album = '".$wpdb->escape($song["Album"])."', ipod_track_genre = '".$wpdb->escape($song['Genre'])."', ipod_track_kind = '".$wpdb->escape($song['Kind'])."', ipod_track_size = '".$wpdb->escape($song['Size'])."', ipod_track_total_time = '".$wpdb->escape($song['Total Time'])."', ipod_track_number = '".$wpdb->escape($song['Track Number'])."', ipod_track_date_modified = '".$wpdb->escape($song['Date Modified'])."', ipod_track_date_added = '".$wpdb->escape($song['Date Added'])."', ipod_track_play_count = '". $wpdb->escape($song['Play Count'])."', ipod_track_bitrate = '".$wpdb->escape($song['Bit Rate'])."', ipod_track_play_date = '".$wpdb->escape($song['Play Date'])."', ipod_track_play_date_utc = '".$wpdb->escape($song['Play Date UTC'])."', ipod_track_rating = '".$wpdb->escape($song['Rating'])."', ipod_track_normalization = '".$wpdb->escape($song['Normalization'])."', ipod_track_location = '".$wpdb->escape($song['Location'])."', ipod_track_file_folder_count = '".$wpdb->escape($song['File Folder Count'])."', ipod_track_library_folder_count = '".$wpdb->escape($song['Library Folder Count'])."'";
	
	return $wpdb->query($sql);
}

/**
 * Takes a unix timestamp and converts to relative format
 *
 * @param   int  $timestamp	date to start with
 * @return  string
 */
function itunes_data_relative_time($timestamp){

	if(!$timestamp){
		return 'N/A';
	}

	$timestamp = (int)$timestamp;
	$difference = time() - $timestamp;
	$periods = array("sec", "min", "hour", "day", "week","month", "years", "decade");
	$lengths = array("60","60","24","7","4.35","12","10");

	if ($difference > 0) { // this was in the past
		$ending = "ago";
	} else { // this was in the future
		$difference = -$difference;
		$ending = "to go";
	}

	for($j = 0; $difference >= $lengths[$j]; $j++) {
		$difference /= $lengths[$j];
	}

	$difference = round($difference);
	if($difference != 1) {
		$periods[$j].= "s";
	}

	$text = "$difference $periods[$j] $ending";
	return $text;
}

/**
 * Takes a unix timestamp and converts to relative format
 *
 * @param   int  $timestamp	date to start with
 * @return  string
 */
function itunes_data_contextual_help($content){

	$plugin_data = get_plugin_data(__FILE__);

	$plugin_name = $plugin_data['Name'];
	$plugin_description = $plugin_data['Description'];

	if(!isset($_REQUEST['page']) || $_REQUEST['page'] != 'itunes-data/itunes-data.php'){
		return $content;
	}

	$content = <<<HTML
	
<h5>$plugin_name Options</h5>
<p>You can change the number of items you want displayed as well as upload a new iTunes XML file using the below form. </p>
<h5>Notes</h5>
<p>If you upload a new file your existing data will be replaced.<br>
You can upload a zip or xml file.</p>
<h5>Other Help</h5>
<p><a href="http://blog.ericlamb.net/itunes-data" target="_blank">Plugin Home Page</a></p>
<h5>About</h5>
<p>$plugin_description</p>
HTML;
	return $content;
}
register_activation_hook(__FILE__,'itunes_data_install');
register_deactivation_hook(__FILE__, 'itunes_data_deactivate');

add_filter('contextual_help', 'itunes_data_contextual_help', 11, 2);

add_action('admin_menu','itunes_data_admin',1);
add_action('wp_head', 'itunes_data_sidebar_header');
add_action('plugins_loaded', 'itunes_data_sidebar_init');
?>