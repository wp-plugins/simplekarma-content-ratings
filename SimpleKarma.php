<?php 
/* Plugin Name: Simple Karma
URI: http://www.csmonitor.com/
Description: A plugin that will allow users to rate any content item on a WordPress or WordPress MU site. Provides special integration handlers for common content types: comments, posts and bbPress. See the docs directory for usage information ( <a href="../wp-content/plugins/simple-karma/docs/install.html">Install</a>, <a href="../wp-content/plugins/simple-karma/docs/management.html">Management</a> ).
Author: Ashley Mitchell
Version: 1.0
Author URI:  mailto:mitchella1@wit.edu

This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/ 
if (is_admin() == true)
{
	require_once( ABSPATH . "/wp-config.php" );
	require_once( ABSPATH . "wp-content/plugins/simple-karma/admin/page.php" );
	require_once( ABSPATH . "wp-content/plugins/simple-karma/admin/options-page.php" );
}
function createSimpleKarmaTables()
{ 
	global $wpdb;
	global $table_name;
	global $options_table;
	$table_name = $wpdb-> prefix . "simple_karma" ;
	$options_table = $wpdb-> prefix . "simple_karma_options";
   
   //if table doesn't already exsist
   	if ($wpdb-> get_var( "SHOW TABLES LIKE '$options_table'" ) != $options_table)
	{
		//creates wp_simple_karma_option table
		$create_options_table = "CREATE TABLE " . $options_table .  " (
				id int(10) unsigned NOT NULL auto_increment,
		        option_name varchar(100),
				option_value longtext,
				UNIQUE KEY id  (id)
				);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($create_options_table);
		
		$insert = "INSERT INTO " . $options_table .
				" (option_name, option_value) " .
				"VALUES ('threshold',-5)";
				
		$wpdb->query( $insert );
		$insert = "INSERT INTO " . $options_table .
            " (option_name, option_value) " .
            "VALUES ('threshold_message','You have exceeded the threshold.')";
			
		$wpdb->query( $insert );
		$insert = "INSERT INTO " . $options_table .
            " (option_name, option_value) " .
            "VALUES ('bbpress_table','bb_post')";
			
		$wpdb->query( $insert );

	}
	
	if ($wpdb-> get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name)
	{
		//creates wp_simple_karma table
		$sql = "CREATE TABLE " . $table_name .  " (
				id int(10) unsigned NOT NULL auto_increment,
		        object_id int(10) ,
				foreign_table varchar(100),
				karma int(10) ,
				UNIQUE KEY id  (id)
				);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}

function simplekarma_add_js ()
{
    $version = '0.1';
    $wp_url = get_bloginfo( 'wpurl' ) . "/";

    echo "\n\t<!-- Added By SimpleKarma Plugin. Version {$version} -->";
    echo "\n\t<script type='text/javascript' src='{$wp_url}wp-content/plugins/simple-karma/javascript/functions.js'></script>";
    echo "\n\t<!-- End SimpleKarma additions -->\n";
}

function add_comment_anchor()
{
	echo "<a name='" . $comment-> comment_ID . "'> </a>";
}

// Calls createTable function on activation of the plugin
register_activation_hook( __FILE__, 'createSimpleKarmaTables' );
add_action( 'admin_menu', 'SimpleKarma_options' );
add_action( 'wp_head', 'simplekarma_add_js' );
add_action( 'admin_head', 'simplekarma_add_js' );
//add_action( 'admin_menu', 'bbpress_options' );
//add_filter('comments_template', 'add_comment_anchor');

?>
