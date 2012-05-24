<?php
function ctaw_display_action($before_widget, $after_widget, $before_title, $after_title, $hide_title = false){
	$current_categories = array();
	$possible_actions = array();
	$possible_titles = array();
	$alt_actions = array();
	$alt_titles = array();
	
	//Get Parent Post Cateogires
	global $post;
	$categories = get_the_category($post->ID);
	//Set up category names in an array
	foreach ($categories as $category) {
		array_push($current_categories,$category->category_nicename);
	}
	
	//Get all calls to action
	$args = array('post_type' => 'ctaw');
	$ctaws = get_posts($args);
	foreach ($ctaws as $ctaw) {
		$action_categories = array();
		setup_postdata($ctaw);
		//Get call to action categories
		$ctaw_cats = get_the_category($ctaw->ID);
		//Set call to action category names in an array
		foreach ($ctaw_cats as $ctaw_cat){
			array_push($action_categories,$ctaw_cat->category_nicename);
		}
		// either populate an array of possible actions or display an alternative action if page has no categories
		if(count($action_categories)==0){
			array_push($alt_actions,array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
		}else if (array_intersect($current_categories, $action_categories)){
			array_push($possible_actions,array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
		} 
	}
	//display results

	if(count($possible_actions)>0){
		$rand_key = array_rand($possible_actions,1);
		$ctaw_id = $possible_actions[$rand_key][0]; 
		$ctaw_title = $possible_actions[$rand_key][1]; 
		$ctaw_content = $possible_actions[$rand_key][2];
	} else {
		$rand_key = array_rand($alt_actions,1);
		$ctaw_id = $alt_actions[$rand_key][0]; 
		$ctaw_title = $alt_actions[$rand_key][1]; 
		$ctaw_content = $alt_actions[$rand_key][2];
	}
	
	$page = get_option('siteurl');
	$page = get_page_link();
	$symbol = (preg_match('/\?/', $page)) ? '&' : '?';
	$ctaw_content = str_replace('"', '\'', $ctaw_content);
	$ctaw_content = str_replace('href=\'http', 'href=\'' . $page . $symbol . 'ctaw_redirect_' . $ctaw_id . '=http', $ctaw_content);
	
	$content = "";
	
	$content .= $before_widget; 
	if(!$hide_title){
		$content .= $before_title . $ctaw_title . $after_title;
	}
	$content .= $ctaw_content;
	$content .= $after_widget;
	
	ctaw_register_impression($ctaw_id);
	
	return $content;
}

//=============================================
// Create 'Call to Action' Widget
//=============================================
class CTAW_Widget extends WP_Widget {
	
    /** constructor */
    function CTAW_Widget() {
        parent::WP_Widget(false, $name = 'Call To Action Widget');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
		extract( $args );
        echo ctaw_display_action($before_widget, $after_widget, $before_title, $after_title);   
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		
    }

} // class CTAW_Widget 

//=============================================
// Create 'Call to Action' shortcode
//=============================================
function ctaw_create_shortcode() {
	return ctaw_display_action('', '', '', '', true);
}
?>