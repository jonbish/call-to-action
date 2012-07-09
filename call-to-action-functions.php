<?php

function ctaw_do_redirect() {
    if ($qs = $_SERVER['REQUEST_URI']) {
        $pos = strpos($qs, 'ctaw_redirect');
        if (!(false === $pos)) {
            $link = substr($qs, $pos);
            $link = str_replace('ctaw_redirect=', '', $link);

            // Extract the ID and get the link
            $pattern = '/ctaw_redirect_(\d+?)\=/';
            preg_match($pattern, $link, $matches);
            $link = preg_replace($pattern, '', $link);

            // Save click!
            //if (get_option('administer_statistics') == 'true') { 
            $id = $matches[1];
            ctaw_register_click($id);
            //}
            // Redirect
            header("HTTP/1.1 302 Temporary Redirect");
            header("Location:" . $link);
            // I'm outta here!
            exit(1);
        }
    }
}

function ctaw_register_impression($id) {
    if (!is_admin()) {
        if(ctaw_is_bot($_SERVER['HTTP_USER_AGENT']))
            return;
        if (get_post_custom_keys($id) && in_array('ctaw_impressions', get_post_custom_keys($id))) {
            $ctaw_impressions = get_post_meta($id, 'ctaw_impressions', true);
        }
        if (!isset($ctaw_impressions)) {
            $ctaw_impressions = 0;
        }
        $ctaw_impressions++;
        update_post_meta($id, 'ctaw_impressions', $ctaw_impressions);
    }
}

function ctaw_register_click($id) {
    if (!is_admin()) {
        if(ctaw_is_bot($_SERVER['HTTP_USER_AGENT']))
            return;
        if (get_post_custom_keys($id) && in_array('ctaw_clicks', get_post_custom_keys($id))) {
            $ctaw_clicks = get_post_meta($id, 'ctaw_clicks', true);
        }
        if (!isset($ctaw_clicks)) {
            $ctaw_clicks = 0;
        }
        $ctaw_clicks++;
        update_post_meta($id, 'ctaw_clicks', $ctaw_clicks);
    }
}

function ctaw_get_impressions($id) {
    if (get_post_custom_keys($id) && in_array('ctaw_impressions', get_post_custom_keys($id))) {
        return get_post_meta($id, 'ctaw_impressions', true);
    } else {
        return 0;
    }
}

function ctaw_get_clicks($id) {
    if (get_post_custom_keys($id) && in_array('ctaw_clicks', get_post_custom_keys($id))) {
        return get_post_meta($id, 'ctaw_clicks', true);
    } else {
        return 0;
    }
}

function ctaw_is_bot($user_agent) {
    $bots = array("alexa", "appie", "Ask Jeeves", "Baiduspider", "bingbot", "Butterfly", "crawler", "facebookexternalhit", "FAST", "Feedfetcher-Google", "Firefly", "froogle", "Gigabot", "girafabot", "Googlebot", "InfoSeek", "inktomi", "looksmart", "Me.dium", "Mediapartners-Google", "msnbot", "NationalDirectory", "rabaz", "Rankivabot", "Scooter", "Slurp", "Sogou web spider", "Spade", "TechnoratiSnoop", "TECNOSEEK", "Teoma", "TweetmemeBot", "Twiceler", "Twitturls", "URL_Spider_SQL", "WebAlta Crawler", "WebBug", "WebFindBot", "www.galaxy.com", "ZyBorg");

    foreach ($bots as $bot) {
        if (strpos($user_agent, $bot) !== false)
            return true; // Is a bot 
    }
    return false; // Not a bot 
}

?>