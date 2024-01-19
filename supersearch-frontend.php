<?php


function supersearch_enqueue_scripts() {
    wp_enqueue_script('supersearch-autocomplete', plugin_dir_url(__FILE__) . 'supersearch.js', [], '1.2.8', true);
    wp_enqueue_script('featherlight', plugin_dir_url(__FILE__) . 'featherlight.min.js', [], '1.0', true);
}
add_action('wp_enqueue_scripts', 'supersearch_enqueue_scripts');

function supersearch_enqueue_styles() {
    wp_enqueue_style('featherlight', plugin_dir_url(__FILE__) . 'featherlight.min.css');
    wp_enqueue_style('supersearch', plugin_dir_url(__FILE__) . 'supersearch.css');
}
add_action('wp_enqueue_scripts', 'supersearch_enqueue_styles');

/*
* Add a shortcode to display the search input
* Usage: [supersearch]
*/
function supersearch_input_shortcode() {

    // Allow CORS
    add_action('send_headers', function() {
        header("Access-Control-Allow-Origin: https://supersearch.hi-orbit.com");
        header("Content-Security-Policy: frame-ancestors 'self' https://supersearch.hi-orbit.com");
    });

    ob_start(); // Start output buffering
    ?>
    <input type="text" id="supersearch-input" placeholder="Type your search here..." value="" onkeyup="search_query(this);">
    <input type="hidden" id="supersearch-key" value="<?php echo get_option('supersearch_public_key');?>">
    <input type="hidden" id="mobile_top_offset" value="<?php echo get_option('supersearch_mobile_top_offset');?>">
    <input type="hidden" id="desktop_top_offset" value="<?php echo get_option('supersearch_desktop_top_offset');?>">
    <input type="hidden" id="supersearch-id" value="<?php echo $_COOKIE['_super_search_id'] ?? ''; ?>" >
    <?php
    return ob_get_clean(); // Return the buffered content    
}
add_shortcode('supersearch', 'supersearch_input_shortcode');

function supersearch_wp_cookie() {
    if (!isset($_COOKIE['_super_search_id']) && !headers_sent()) {
        $trackingId = generateTrackingId();
        $expiration_time = time() + (90 * 24 * 60 * 60);
        setcookie('_super_search_id', $trackingId, $expiration_time, COOKIEPATH, COOKIE_DOMAIN);
    } 
}
add_action( 'init', 'supersearch_wp_cookie' );

function generateTrackingId() {
    return sprintf(
        '%04x-%04x-%04x-%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

