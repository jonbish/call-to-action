<?php
function ctaw_init() 
{
  $labels = array(
    'name' => __('Calls to Action'),
    'singular_name' => __('Action'),
    'add_new' => __('Add New'),
    'add_new_item' => __('Add New Action'),
    'edit_item' => __('Edit Action'),
    'new_item' => __('New Action'),
    'view_item' => __('View Action'),
    'search_items' => __('Search Actions'),
    'not_found' =>  __('No actions found'),
    'not_found_in_trash' => __('No actions found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => __('Actions'),

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => false,
    'rewrite' => true,
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','author'),
	'taxonomies' => array('category')
  ); 
  
	register_post_type('ctaw',$args);
	global $submenu;
	$submenu['ctaw'][0] = 'Dashboard';

}

function ctaw_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['ctaw'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Action updated. <a href="%s">View action</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Action updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Action restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Action published. <a href="%s">View action</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Action saved.'),
    8 => sprintf( __('Action submitted. <a target="_blank" href="%s">Preview action</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Action scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview action</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Action draft updated. <a target="_blank" href="%s">Preview action</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

function ctaw_add_help_text($contextual_help, $screen_id, $screen) { 
  //$contextual_help .= var_dump($screen); // use this to help determine $screen->id
  if ('ctaw' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Things to remember when adding or editing an action:') . '</p>' .
      '<ul>' .
      '<li>' . __('Create Urgency.') . '</li>' .
      '<li>' . __('Use Numbers.') . '</li>' .
	  '<li>' . __('Indicate a Specific Action.') . '</li>' .
	  '<li>' . __('Use Images .') . '</li>' .
	  '<li>' . __('Use Contrasting Colors.') . '</li>' .
      '</ul>';
  } elseif ( 'edit-ctaw' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('This is the help screen displaying the table of actions blah blah blah.') . '</p>' ;
  }
  return $contextual_help;
}

function ctaw_columns($columns)
{
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Action Title",
		"impressions" => "Impressions",
		"clicks" => "Clicks",
		"author" => "Author",
		"categories" => "Categories",
		"date" => "Date"
	);
	return $columns;
}

function ctaw_column($column)
{
	global $post;
	if ("ID" == $column) echo $post->ID;
	elseif ("impressions" == $column) echo ctaw_get_impressions($post->ID);
	elseif ("clicks" == $column)  echo ctaw_get_clicks($post->ID);
}
// Add the sorting SQL
function ctaw_column_orderby($orderby, $wp_query) {
	global $wpdb;
 
	$wp_query->query = wp_parse_args($wp_query->query);
 
	if ( 'impressions' == @$wp_query->query['orderby'] )
		$orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'ctaw_impressions') " . $wp_query->get('order');
 	
	if ( 'clicks' == @$wp_query->query['orderby'] )
		$orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'ctaw_clicks') " . $wp_query->get('order');
		
	return $orderby;
}
// Register the column as sortable
function ctaw_column_register_sortable($columns) {
	$columns['impressions'] = 'impressions';
 	$columns['clicks'] = 'clicks';
	return $columns;
}

function ctaw_display_options(){
    return array(
        '_is_all_ctaw'  => 'Every Page',
        '_is_front_ctaw' => 'Static Front Page',
        '_is_page_ctaw' => 'Single Page',
        '_is_home_ctaw' => 'Blog Page',
        '_is_single_ctaw' => 'Single Post',
        '_is_archive_ctaw' => 'Archive',
        '_is_author_ctaw' => 'Author Archive',
        '_is_404_ctaw' => '404 Page',
        '_is_search_ctaw' => 'Search Page'
    );
}
// Meta box
function ctaw_add_meta_box() {
        add_meta_box('ctaw-buttons-meta', __('Call To Action Display', 'ctaw'),  'ctaw_metabox_admin', 'ctaw', 'side');
}

function ctaw_metabox_admin() {
    global $post;
    $display_options = ctaw_display_options();
    
    $default_content = "";
    $default_content .= '<input type="hidden" name="ctaw_settings_noncename" id="ctaw_settings_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    $default_content .= '<ul id="inline-sortable">';
    foreach ($display_options as $ctaw_display=>$ctaw_name) {
        $default_content .= '<li class="ui-state-default"><label class="selectit"><input value="1" type="checkbox" name="'.$ctaw_display.'" id="post-share-' . $ctaw_display . '"' . checked(get_post_meta($post->ID, $ctaw_display, true), 1, false) . '/> <span>' . __($ctaw_name) . '</span></label></li>';
    }
    $default_content .= '</ul>';
    echo $default_content;
}

//=============================================
// On save post, update post meta
//=============================================
function ctaw_admin_process($post_ID) {
    if (!isset($_POST['ctaw_settings_noncename']) || !wp_verify_nonce($_POST['ctaw_settings_noncename'], plugin_basename(__FILE__))) {
        return $post_ID;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_ID;

    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_ID))
            return $post_ID;
    } else {
        if (!current_user_can('edit_post', $post_ID))
            return $post_ID;
    }

    $ctawmetaarray = array();
    $ctawmetaarray_text = "";
    
    if (isset($_POST['hide_alert']) && ($_POST['hide_alert'] > 0)) {
        array_push($ctawmetaarray, $_POST['hide_alert']);
    }
    if (isset($_POST['ctaw_text']) && ($_POST['ctaw_text'] != "")) {
        $ctawmetaarray_text = $_POST['ctaw_text'];
    }
    if (isset($_POST['ctaw_buttons'])) {
        foreach ($_POST['ctaw_buttons'] as $button) {
            if (($button > 0)) {
                array_push($ctawmetaarray, $button);
            }
            $formid++;
        }
    }
    $ctawmeta = implode(',', $ctawmetaarray);

    if (!wp_is_post_revision($post_ID) && !wp_is_post_autosave($post_ID)) {
        $display_options = ctaw_display_options();
        foreach ($display_options as $ctaw_display=>$ctaw_name) {
            if (isset($_POST[$ctaw_display]) && $_POST[$ctaw_display] != ''){
                update_post_meta($post_ID, $ctaw_display, 1);
            } else {
                update_post_meta($post_ID, $ctaw_display, 0);
            }
        }
    }
}
?>