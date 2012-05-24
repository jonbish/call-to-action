<?php
/*
Plugin Name: Call to Action
Plugin URI: http://www.jonbishop.com/downloads/wordpress-plugins/call-to-action
Description: Displays the most relavent Call to Action in your sidebar based on the content of the page
Version: 1.2
Author: Jon Bishop
Author URI: http://www.jonbishop.com
License: GPL2
*/
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : jbishop@hubspot.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//=============================================
// Define constants
//=============================================
define('CTAW_URL', plugin_dir_url(__FILE__));
define('CTAW_PATH', plugin_dir_path(__FILE__));
define('CTAW_BASENAME', plugin_basename( __FILE__ ));
define('CTAW_ADMIN', get_bloginfo('url')."/wp-admin");

//=============================================
// Include needed files
//=============================================
require_once(CTAW_PATH."/call-to-action-functions.php");
require_once(CTAW_PATH."/call-to-action-display.php");
require_once(CTAW_PATH."/call-to-action-admin.php");

//=============================================
// Add shortcodes, filters and actions
//=============================================
add_shortcode('ctaw', 'ctaw_create_shortcode');
add_action('widgets_init', create_function('', 'return register_widget("CTAW_Widget");'));
add_action('init', 'ctaw_init');
//add filter to insure the text Action, or action, is displayed when user updates an action 
add_filter('post_updated_messages', 'ctaw_updated_messages');
//display contextual help for Actions
add_action( 'contextual_help', 'ctaw_add_help_text', 10, 3 );
// Count the number of impressions the content makes
add_action('init', 'ctaw_do_redirect', 11);
// Columns
add_filter('manage_edit-ctaw_sortable_columns', 'ctaw_column_register_sortable');
add_filter('posts_orderby', 'ctaw_column_orderby', 10, 2);
add_action("manage_posts_custom_column", "ctaw_column");
add_filter("manage_edit-ctaw_columns", "ctaw_columns");

?>