<?php
/**
 * iTunes-Data Install Module
 *
 * Contains all iTunes-Data functions for the installation routine.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    iTunes-Data
 * @version 1.0
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

/**
 * Installs the database tables and settings
 *
 * @return  void
 */
function itunes_data_install () {
	global $wpdb;
	global $itunes_data_db_version;
	global $itunes_data_version;

	//install tracking_clicks table
	$table_name = $wpdb->itunes_data_table_name;
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE IF NOT EXISTS `wp_itunes` (
		  `ipod_track_id` int(10) NOT NULL auto_increment,
		  `ipod_track_name` varchar(255) NOT NULL default '',
		  `ipod_track_artist` varchar(255) NOT NULL default '',
		  `ipod_track_album` varchar(255) NOT NULL default '',
		  `ipod_track_genre` varchar(255) NOT NULL default '',
		  `ipod_track_kind` varchar(255) NOT NULL default '',
		  `ipod_track_size` int(15) NOT NULL default '0',
		  `ipod_track_total_time` int(15) NOT NULL default '0',
		  `ipod_track_number` int(4) NOT NULL default '0',
		  `ipod_track_date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
		  `ipod_track_date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		  `ipod_track_bitrate` int(4) NOT NULL default '0',
		  `ipod_track_play_count` int(10) NOT NULL default '0',
		  `ipod_track_play_date` int(15) NOT NULL default '0',
		  `ipod_track_play_date_utc` datetime NOT NULL default '0000-00-00 00:00:00',
		  `ipod_track_rating` int(10) NOT NULL default '0',
		  `ipod_track_normalization` int(10) NOT NULL default '0',
		  `ipod_track_location` varchar(255) NOT NULL default '',
		  `ipod_track_file_folder_count` int(2) NOT NULL default '0',
		  `ipod_track_library_folder_count` int(2) NOT NULL default '0',
		  PRIMARY KEY  (`ipod_track_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='stores all info from itunes playlist' AUTO_INCREMENT=0 ;
		";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	$options['version'] = '1.0';
	$options['db_version'] = '1.0';
	$options['num_display'] = '10';
	$options['last_modified'] = mktime();
	$options['last_uploaded'] = 0;
	add_option("itunes_data", $options);

	include 'activation-client.php';
	activation_counter_send_notice('http://blog.ericlamb.net/wp-plugin-activation-post','itunes-data',$options['version'],$options['db_version'],1);
}

/**
 * Handles the removal of a plugin
 *
 * @return  void
 */
function itunes_data_deactivate(){
	global $wpdb;
	global $itunes_data_db_version;
	global $itunes_data_version;
	include 'activation-client.php';

	activation_counter_send_notice('http://blog.ericlamb.net/wp-plugin-activation-post','itunes-data',$itunes_data_version,$itunes_data_db_version,0);

	$table_name = $wpdb->itunes_data_table_name;
	if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
		$sql = "DROP TABLE $table_name ";
		$wpdb->query($sql);

		$sql = "DELETE FROM ".$wpdb->prefix ."options WHERE option_name = 'itunes_data'";
		$wpdb->query($sql);
	}
}

?>