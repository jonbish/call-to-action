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
?>