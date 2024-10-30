<?php
/**
 * Activation Client
 *
 * Performs the POST notification. 
 * Should be placed in the same directory as the wp plugin you want to monitor.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-activation-counter
 * @version 0.1
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

if(!function_exists('activation_counter_send_notice')){
	/**
	 * Sends the activation notice
	 *
	 * @param   string  $url		fully resolved URI to notify activation to
	 * @param   string  $name		plugin name activation is for
	 * @param   string  $version	version of the plugin activation is for
	 * @param   string  $db_version	database version for the plugin notice
	 * @param   int		$type		notice type. 1 = activation, 2 = deactivation
	 * @return  void
	 */
	function activation_counter_send_notice($url, $plugin, $version, $db_version = FALSE, $type = 1){
		
		$date = date('r');
		$ident = md5(get_option('siteurl'));
		$blogname = get_option('blogname');
		$blogurl = get_option('siteurl');
		$email = get_option('admin_email');
		//return TRUE;
		
		$post_data = array (
			'headers'	=> null,
			'body'		=> array(
				'ident'	=> $ident,
				'blogname' 	=> base64_encode($blogname),
				'blogurl'	=> base64_encode($blogurl),
				'email'		=> base64_encode($email),
				'type'		=> $type,
				'plugin'	=> base64_encode($plugin),
				'db_version'	=> $db_version,
				'version'	=> $version
			),
		);

		wp_remote_post($url, $post_data);
	}
}
?>