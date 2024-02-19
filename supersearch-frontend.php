<?php
/**
 * WordPress plugin "SuperSearch" front-end file, responsible for initiating the front-end website code.
 *
 * @package SuperSearch
 * @author Rob Locke
 * @version 1.0.9
 * 
 * 
 * Plugin Name: SuperSearch
 * Description: SuperSearch provides a hyperfast search solution for your WordPress site.
 * Version: 1.0.9
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Rob Locke
 * Author URI: https://www.hi-orbit.com
 * Author email: hello@hi-orbit.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * SuperSearch for WordPress is a free plugin and you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as published by
 * the Free Software Foundation.
 * 
 * SuperSearch for WordPress is distributed to provide an integration with,
 * https://supersearch.hi-orbit.com but provided WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function supersearch_enqueue_scripts() {
    wp_enqueue_script('supersearch-js', plugin_dir_url(__FILE__) . 'supersearch.js', array('jquery'), '1.3.8', true);
    wp_enqueue_script('featherlight', plugin_dir_url(__FILE__) . 'featherlight-1.7.13/release/featherlight.min.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'supersearch_enqueue_scripts');

function supersearch_enqueue_styles() {
    wp_enqueue_style('featherlight', plugin_dir_url(__FILE__) . 'featherlight-1.7.13/release/featherlight.min.css');
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

    $active_tab = sanitize_text_field(get_option('supersearch_public_key'));
    $active_tab = esc_attr($active_tab); // Escape for HTML attribute


    ob_start(); // Start output buffering
    ?>
    <input type="text" id="supersearch-input" placeholder="Type your search here..." value="" onkeyup="search_query(this);" autocomplete="off">
    <input type="hidden" id="supersearch-key" value="<?php echo esc_attr(get_option('supersearch_public_key')); ?>">
    <input type="hidden" id="mobile_top_offset" value="<?php echo esc_attr(get_option('supersearch_mobile_top_offset'));?>">
    <input type="hidden" id="desktop_top_offset" value="<?php echo esc_attr(get_option('supersearch_desktop_top_offset'));?>">
    <input type="hidden" id="supersearch-id" value="" >
    <?php
    return ob_get_clean(); // Return the buffered content    
}
add_shortcode('supersearch', 'supersearch_input_shortcode');