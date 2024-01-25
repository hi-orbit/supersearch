<?php

/**
 * Plugin Name: WP Super Search
 * Description: Super Search provides a hyperfast search solution for your WordPress site.
 * Version: 1.0.5
 * Author: https://www.hi-orbit.com
 * Plugin URI: https://www.hi-orbit.com
 * Requires at least: 6.3
 * Requires PHP: 8.0
 */

define('_SEARCH_URL', 'https://supersearch.hi-orbit.com/api/search/');

/**
 * Add a menu item to the admin menu
 */
add_action('admin_menu', 'wp_supersearch_settings_menu');
function wp_supersearch_settings_menu()
{
    add_options_page(
        'Super Search Settings',       // Page title
        'Super Search',                // Menu title
        'manage_options',              // Capability
        'wp-supersearch-settings',        // Menu slug
        'wp_supersearch_settings_page'    // Callback function
    );
}

/**
 * Add tabs to the admin plugin settings options page
 */
function wp_supersearch_add_tabs() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'usage';

    ?>
    <h1>Super Search Settings</h1>
    <p>Super Search provides a hyperfast search solution for your WordPress site.</p>
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-supersearch-settings&tab=usage" class="nav-tab <?php echo $active_tab == 'usage' ? 'nav-tab-active' : ''; ?>">Account Usage</a>
        <a href="?page=wp-supersearch-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        <a href="?page=wp-supersearch-settings&tab=sync" class="nav-tab <?php echo $active_tab == 'sync' ? 'nav-tab-active' : ''; ?>">Sync</a>
    </h2>
    <?php
}

/**
 * Display the admin plugin settings options page
 */
function wp_supersearch_settings_page()
{
    wp_supersearch_add_tabs();
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'usage';

    $key = 'supersearch_usage_stats';
    $stats = wp_cache_get($key);
    if (!$stats){
        $stats = json_decode(wp_supersearch_perform_curl_request('', 'usagestats')) ?? false;
        wp_cache_set($key,$stats,'',900);
    }
    ?>
    <script>
        searchitem_progress = <?php echo $stats->data->search_item_percent ?? 0;?>;
        searchlimit_progress = <?php echo $stats->data->search_percent ?? 0;?>;
    </script>
    
    <div style="content">
        <?php if ($active_tab == 'usage') { ?>
            <img src="https://supersearch.hi-orbit.com/assets/images/supersearch_200x200.png" style="float:right;margin-left:20px;">
            <h2>Current Plan Usage</h2>
                <?php
                if ($stats){ 
                ?>
                Search count: <div style="width:300px;border: 1px solid grey;"><div id="searchlimit-progress-bar" style="width: 0%; height: 20px; background-color: green;"></div></div>
                <?php echo $stats->data->search_count; ?> of <?php echo  $stats->data->search_limit; ?>
                <br><br>
                Search index: <div style="width:300px;border: 1px solid grey;"><div id="searchitem-progress-bar" style="width: 0%; height: 20px; background-color: green;"></div></div>
                <?php echo $stats->data->search_item_count; ?> of <?php echo  $stats->data->search_item_limit; ?>
                <p><strong>Manage your plan <a href="https://supersearch.hi-orbit.com/admin/plans" target="_blank">here</a></strong></p>
                <?php
                } else {
                    echo '<p>Unable to retrieve usage stats.</p>';
                }
                ?>

        <?php } else if ($active_tab == 'settings') { ?>
                <h2>Settings</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('wp-supersearch-settings-group');
                    do_settings_sections('wp-supersearch-settings');
                    submit_button('Save Settings');
                    ?>
                </form>
        <?php } else if ($active_tab == 'sync') { ?>
        <h2>Sync Products, Posts, and Pages to Super Search search index</h2>
        <p>We track when products, posts and pages are created and updated and once a day we automatically sync changes to our search index. If you want to sync everything immediately, click the buttons below.</p>
        <p><strong>Note: Please keep this page open to complete the sync. This process may take a while to complete.</strong></p>
        <button class="button button-secondary" id="start-products-process">Sync Products To Search Index</button>
        <div style="margin-top:10px;margin-bottom:10px;">
            <div style="width:300px;border: 1px solid grey;"><div id="products-progress-bar" style="width: 0%; height: 20px; background-color: green;"></div></div>
            <div id="products-progress-status">0% - not started</div>
            <div id="products-count">products sync not started</div>
        </div>
        <hr>
        <button class="button button-secondary" id="start-posts-process">Sync Posts To Search Index</button>
        <div style="margin-top:10px;margin-bottom:10px;">
            <div style="width:300px;border: 1px solid grey;"><div id="posts-progress-bar" style="width: 0%; height: 20px; background-color: green;"></div></div>
            <div id="posts-progress-status">0% - not started</div>
            <div id="posts-count">posts sync not started</div>
        </div>
        <hr>
        <button class="button button-secondary" id="start-pages-process">Sync Pages To Search Index</button>
        <div style="margin-top:10px;margin-bottom:10px;">
            <div style="width:300px;border: 1px solid grey;"><div id="pages-progress-bar" style="width: 0%; height: 20px; background-color: green;"></div></div>
            <div id="pages-progress-status">0% - not started</div>
            <div id="pages-count">pages sync not started</div>
        </div>
        <?php } ?>
        <div id='sup-type'></div>
        <p>Your data is safe! - <a href="https://hi-orbit.com/supersearch/privacy" target="_blank">read about our privacy policy and how we process your data.</a></p>
    </div>
<?php
}

// include js and css

/**
 * Enqueue scripts
 */
function wp_supersearch_admin_enqueue_scripts()
{
    wp_enqueue_script('wp-supersearch-admin-js', plugin_dir_url(__FILE__) . 'wp-supersearch-admin.js', array('jquery'));
    wp_localize_script('wp-supersearch-admin-js', 'wpSupersearch', array(
        'ajax_nonce' => wp_create_nonce('wp_supersearch_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'wp_supersearch_admin_enqueue_scripts');

/**
 * Enqueue styles
 */
function wp_supersearch_admin_enqueue_styles()
{
    wp_enqueue_style('wp-supersearch-admin-style', plugin_dir_url(__FILE__) . 'wp-supersearch-admin.css');
}
add_action('admin_enqueue_scripts', 'wp_supersearch_admin_enqueue_styles');

function supersearch_process_posts_handler()
{
    check_ajax_referer('wp_supersearch_nonce', 'nonce');

    $batch_size = 10;

    $page = 0;
    if (isset($_POST['page'])) {
        $page = sanitize_text_field($_POST['page']);
    }
    if (isset($_POST['product_count'])) {
        $product_count = sanitize_text_field($_POST['product_count']);
    }
    if (isset($_POST['post_type'])) {
        $post_type = sanitize_text_field($_POST['post_type']);
    }


    // Get all posts
    $data = supersearch_get_paginated_data($post_type, $page, $batch_size);
    $posts = $data['posts'];

    $product_count = (isset($product_count)) ? $product_count + count($posts) : count($posts);

    $language_code = get_locale();
    $response = wp_supersearch_perform_curl_request($posts, 'createupdate?transform=wp&language=' . $language_code);
    $response = json_decode($response);
    if (isset($response->status_code) && $response->status_code == 508) {
        wp_send_json_error($response->message, 508);
    }

    $page = $page + 1;
    $total_posts = $data['total_items'];
    $progress = round(($page * $batch_size) / $total_posts * 100);
    wp_send_json_success(['progress' => $progress, 'page' => $page, 'product_count' => $product_count, 'total_posts' => $total_posts]);
}
add_action('wp_ajax_process_posts', 'supersearch_process_posts_handler');

function supersearch_get_paginated_data($post_type, $page_number = 1, $posts_per_page = 10)
{

    switch ($post_type) {
        case 'products':
            $request = new WP_REST_Request('GET', '/wc/v3/products');
            break;
        case 'posts':
            $request = new WP_REST_Request('GET', '/wp/v2/posts');
            break;
        case 'pages':
            $request = new WP_REST_Request('GET', '/wp/v2/pages');
            break;
        default:
            $request = new WP_REST_Request('GET', '/wp/v2/posts');
            break;
    }

    $request->set_query_params(['per_page' => intval($posts_per_page), 'page' => intval($page_number)]);
    $response = rest_do_request($request);
    $data = rest_get_server()->response_to_data($response, false);

    return array(
        'posts' => $data,
        'total_items' => $response->headers['X-WP-Total'],
        'total_pages' => $response->headers['X-WP-TotalPages']
    );
}

// Add featured image URL to API response
function add_featured_image_url_to_api_response($response)
{
    $featured_image_id = $response->data['featured_media'];
    if ($featured_image_id) {
        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'medium');
        $response->data['featured_image_url'] = $featured_image_url;
    }
    return $response;
}
add_filter('rest_prepare_post', 'add_featured_image_url_to_api_response', 10, 3);

// Add the category information to the API response
function add_category_info_to_api_response($response, $post)
{
    $category = get_the_category($post->ID);
    $response->data['categories'][0] = ['name' => $category[0]->name, 'slug' => $category[0]->slug];
    return $response;
}
add_filter('rest_prepare_post', 'add_category_info_to_api_response', 10, 3);

function add_plain_text_excerpt_to_api_response($response)
{
    if (isset($response->data['excerpt']['rendered'])) {
        $content = strip_shortcodes($response->data['excerpt']['rendered']);
        $plain_text_excerpt = wp_strip_all_tags($content);
        $plain_text_excerpt = html_entity_decode($plain_text_excerpt);
        $plain_text_excerpt = preg_replace('/\[\/?et_pb.*?\]/', '', $plain_text_excerpt);

        $response->data['excerpt']['plain_text'] = $plain_text_excerpt;
    }
    return $response;
}
add_filter('rest_prepare_post', 'add_plain_text_excerpt_to_api_response', 10, 3);
add_filter('rest_prepare_page', 'add_plain_text_excerpt_to_api_response', 10, 3);


// Register settings, a section, and fields
add_action('admin_init', 'wp_supersearch_settings_init');
function wp_supersearch_settings_init()
{
    register_setting('wp-supersearch-settings-group', 'wp_supersearch_public_key');
    register_setting('wp-supersearch-settings-group', 'wp_supersearch_private_key');
    register_setting('wp-supersearch-settings-group', 'wp_supersearch_desktop_top_offset');
    register_setting('wp-supersearch-settings-group', 'wp_supersearch_mobile_top_offset');

    add_settings_section(
        'wp-supersearch-settings-section',
        'API Keys',
        'wp_supersearch_settings_section_callback',
        'wp-supersearch-settings'
    );

    add_settings_field(
        'wp-supersearch-public-key-field',
        'Public Key',
        'wp_supersearch_settings_public_key_field_callback',
        'wp-supersearch-settings',
        'wp-supersearch-settings-section'
    );
    add_settings_field(
        'wp-supersearch-private-key-field',
        'Private Key',
        'wp_supersearch_settings_private_key_field_callback',
        'wp-supersearch-settings',
        'wp-supersearch-settings-section'
    );
    add_settings_section(
        'wp-supersearch-position-section',
        'Search Window Position',
        'wp_supersearch_position_section_callback',
        'wp-supersearch-settings'
    );
    add_settings_field(
        'wp-supersearch-desktop-top-offset-field',
        'Desktop Search Window Top Offset',
        'wp_supersearch_settings_desktop_top_offset_field_callback',
        'wp-supersearch-settings',
        'wp-supersearch-position-section'
    );
    add_settings_field(
        'wp-supersearch-mobile-top-offset-field',
        'Mobile Search Window Top Offset',
        'wp_supersearch_settings_mobile_top_offset_field_callback',
        'wp-supersearch-settings',
        'wp-supersearch-position-section'
    );
}

function wp_supersearch_settings_section_callback()
{
    echo '<p><strong>Get your API Keys by creating an account <a href="https://supersearch.hi-orbit.com" target="_blank">here</a></strong></p><p>Enter your API keys below.</p>';
}

function wp_supersearch_position_section_callback()
{
    echo '<p>Enter the top offset for the search window below.</p>';
}

function wp_supersearch_settings_public_key_field_callback()
{
    $public_key = get_option('wp_supersearch_public_key');
    echo '<input type="text" id="wp_supersearch_public_key" name="wp_supersearch_public_key" value="' . esc_attr($public_key) . '" size="40"/>';
}

function wp_supersearch_settings_private_key_field_callback()
{
    $private_key = get_option('wp_supersearch_private_key');
    echo '<input type="text" id="wp_supersearch_private_key" name="wp_supersearch_private_key" value="' . esc_attr($private_key) . '" size="40"/>';
}

function wp_supersearch_settings_desktop_top_offset_field_callback()
{
    $desktop_top_offset = get_option('wp_supersearch_desktop_top_offset');
    if (!isset($desktop_top_offset) || $desktop_top_offset == '' || $desktop_top_offset == null) {
        $desktop_top_offset = 43;
    }
    echo '<input type="text" id="wp_supersearch_desktop_top_offset" name="wp_supersearch_desktop_top_offset" value="' . esc_attr($desktop_top_offset) . '" size="6" />';
}
function wp_supersearch_settings_mobile_top_offset_field_callback()
{
    $mobile_top_offset = get_option('wp_supersearch_mobile_top_offset');
    if (!isset($mobile_top_offset) || $mobile_top_offset == '' || $mobile_top_offset == null) {
        $mobile_top_offset = 149;
    }
    echo '<input type="text" id="wp_supersearch_mobile_top_offset" name="wp_supersearch_mobile_top_offset" value="' . esc_attr($mobile_top_offset) . '" size="6" />';
}

/**
 * Save post event, that records the post ID of changed posts
 */
function wp_supersearch_record_changed_posts( $post_id, $post, $update ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // get the saved changed posts...
    $changed_posts = get_option( 'supersearch_changed_posts', array() );

    // Add the post ID to your collection if it's not already there
    if ( ! in_array( $post_id, $changed_posts ) ) {
        $changed_posts[] = $post_id;
        update_option( 'supersearch_changed_posts', $changed_posts );
    }
}
add_action( 'save_post', 'wp_supersearch_record_changed_posts', 10, 3 );

// crontab 

/**
 * Add a cron event to run daily
 */
function wp_supersearch_update_function(){
    wp_supersearch_log( 'wp_supersearch_daily_index_update - called' );
    $changed_posts = get_option( 'supersearch_changed_posts', array() );
    wp_supersearch_log('changed_posts: ' . json_encode($changed_posts));
    if (count($changed_posts) > 0){
        $posts = get_posts([
            'post_type' => 'any',
            'post__in' => $changed_posts,
            'posts_per_page' => -1
        ]);

        $language_code = get_locale();

        $chunked_posts = array_chunk($posts, 10);
        foreach ($chunked_posts as $chunk){
            $response = wp_supersearch_perform_curl_request($chunk, 'createupdate?transform=wp&language=' . $language_code);
            wp_supersearch_log('response: ' . json_encode($response) );
        }

        update_option( 'supersearch_changed_posts', array() );
    }
}
add_action( 'wp_supersearch_daily_index_update', 'wp_supersearch_update_function');

/**
 * Log to a file
 */
function wp_supersearch_log( $message ) {
    if ( true === WP_DEBUG ) {
        $log_file = plugin_dir_path( __FILE__ ) . 'supersearch.log';
        error_log( date( "Y-m-d H:i:s" ) . ": " . $message . "\n", 3, $log_file );
    }
}

/**
 * Curl request to SuperSearch API
 */
function wp_supersearch_perform_curl_request($data, $action)
{

    $url = _SEARCH_URL . $action;
    $public_key = get_option('wp_supersearch_public_key');
    $private_key = get_option('wp_supersearch_private_key');
    $token = md5($public_key . $private_key);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'public_key' => $public_key,
        'token' => $token,
        'data' => json_encode($data)
    )));

    $response = curl_exec($ch);
    curl_close($ch);

    return $response ?: 'No response from server';
}

// activate and deactivate hooks

/**
 * Activate supersearch plugin
 */
function wp_supersearch_activate() {
    if ( ! wp_next_scheduled( 'wp_supersearch_daily_index_update' ) ) {
        wp_schedule_event( time(), 'daily', 'wp_supersearch_daily_index_update' );
    }
}
register_activation_hook( __FILE__, 'wp_supersearch_activate' );

/**
 * Deactivate supersearch plugin
 */
function wp_supersearch_deactivate() {
    $timestamp = wp_next_scheduled( 'wp_supersearch_daily_index_update' );
    wp_unschedule_event( $timestamp, 'wp_supersearch_daily_index_update' );
}

register_deactivation_hook( __FILE__, 'wp_supersearch_deactivate' );

// Add a shortcode to display the search input

include 'wp-supersearch-frontend.php';
