<?php

function ctaw_display_action($before_widget, $after_widget, $before_title, $after_title, $hide_title = false) {
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
        array_push($current_categories, $category->category_nicename);
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
        foreach ($ctaw_cats as $ctaw_cat) {
            array_push($action_categories, $ctaw_cat->category_nicename);
        }
        
        // either populate an array of possible actions or display an alternative action if page has no categories
        $every_page = get_post_meta($ctaw->ID, '_is_all_ctaw', true);
        $front_page = get_post_meta($ctaw->ID, '_is_front_ctaw', true);
        $single_page = get_post_meta($ctaw->ID, '_is_page_ctaw', true);
        $home_page = get_post_meta($ctaw->ID, '_is_home_ctaw', true);
        $single_post = get_post_meta($ctaw->ID, '_is_single_ctaw', true);
        $archive_page = get_post_meta($ctaw->ID, '_is_archive_ctaw', true);
        $author_page = get_post_meta($ctaw->ID, '_is_author_ctaw', true);
        $error_page = get_post_meta($ctaw->ID, '_is_404_ctaw', true);
        $search_page = get_post_meta($ctaw->ID, '_is_search_ctaw', true);
        
        if($every_page==1){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($front_page==1 && is_front_page()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($single_page==1 && is_page()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($home_page==1 && is_home()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($archive_page==1 && is_archive()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($author_page==1 && is_author()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($error_page==1 && is_404()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if($search_page==1 && is_search()){
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else if (is_single() && $single_post==1 && array_intersect($current_categories, $action_categories)) {
            array_push($possible_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        } else {
            array_push($alt_actions, array($ctaw->ID, $ctaw->post_title, $ctaw->post_content));
        }
    }
    
    // Reset post data to keep other plugins happy
    wp_reset_postdata();
    
    // Remove duplicates
    $possible_actions = array_map("unserialize", array_unique(array_map("serialize", $possible_actions)));
    $alt_actions = array_map("unserialize", array_unique(array_map("serialize", $alt_actions)));
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

    $ctaw_id = $possible_actions[$rand_key][0];
    $ctaw_title = $possible_actions[$rand_key][1];
    $ctaw_content = $possible_actions[$rand_key][2];

    $page = get_option('siteurl');
    $page = get_page_link();
    $symbol = (preg_match('/\?/', $page)) ? '&' : '?';
    $ctaw_content = str_replace('"', '\'', $ctaw_content);
    $ctaw_content = str_replace('href=\'http', 'href=\'' . $page . $symbol . 'ctaw_redirect_' . $ctaw_id . '=http', $ctaw_content);

    $content = "";

    $content .= $before_widget;
    if (!$hide_title) {
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
        extract($args);
        $hide_title = $instance['hide_title'] ? '1' : '0';
        echo ctaw_display_action($before_widget, $after_widget, $before_title, $after_title, $hide_title);
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['hide_title'] = $new_instance['hide_title'] ? 1 : 0;
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $default_instance = array('hide_title' => '');
        ?>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['hide_title'], '1'); ?> id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" /> <label for="<?php echo $this->get_field_id('hide_title'); ?>"><?php _e('Hide Title'); ?></label></p>
        <?php
    }

}

// class CTAW_Widget 
//=============================================
// Create 'Call to Action' shortcode
//=============================================
function ctaw_create_shortcode() {
    return ctaw_display_action('', '', '', '', true);
}
?>