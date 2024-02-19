/**
* WordPress plugin "SuperSearch" front-end javascript file.
* See supersearch.php for version and license information
*/
jQuery(document).ready(function($) {

    const batch_id = 'xxxxxxxx-xxxx-4xxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });;

    // set the usage progress bar
    $('#searchlimit-progress-bar').css('width', searchlimit_progress + '%');
    $('#searchitem-progress-bar').css('width', searchitem_progress + '%');

    if (searchlimit_progress >= 80){
        $('#searchlimit-progress-bar').css('background-color', 'red');
    }
    if (searchitem_progress >= 80){
        $('#searchitem-progress-bar').css('background-color', 'red');
    }

    $('#start-products-process').on('click', function() {
        var data = {
            'action': 'process_posts',
            'nonce': supersearch.ajax_nonce // Pass nonce for security
        };
        supersearch_process_posts('products', data);
    });
    $('#start-posts-process').on('click', function() {
        var data = {
            'action': 'process_posts',
            'nonce': supersearch.ajax_nonce // Pass nonce for security
        };
        supersearch_process_posts('posts', data);
    });
    $('#start-pages-process').on('click', function() {
        var data = {
            'action': 'process_posts',
            'nonce': supersearch.ajax_nonce // Pass nonce for security
        };
        supersearch_process_posts('pages', data);
    });
    
    function supersearch_update_progress_bar(progress, post_type, processed_count) {
        $('#' + post_type + '-progress-bar').css('width', progress + '%');
        $('#' + post_type + '-progress-status').text(progress + '% completed');
        document.getElementById(post_type + '-count').innerHTML = processed_count + ' ' + post_type + ' processed';
    }
    
    function supersearch_process_posts(post_type = 'products',data, page = 1,progress = 0,product_count = 0) {
        data.page = page;
        data.product_count = product_count;
        data.post_type = post_type;
        data.batch_id = batch_id;
    
        supersearch_update_progress_bar(progress,post_type,product_count);
    
        $.ajax({
            url: ajaxurl, // ajaxurl is automatically defined by WordPress
            type: 'POST',
            data: data,
            success: function(response) {

                if (response.success === false) {
                    $('#' + post_type + '-progress-status').html(response.data);
                    return;
                }
                
                supersearch_update_progress_bar(response.data.progress,post_type,response.data.product_count);
    
                if (response.data.product_count < response.data.total_posts) {
                    supersearch_process_posts(data.post_type, data, response.data.page, response.data.progress, response.data.product_count);
                } else {
                    supersearch_update_progress_bar(100, data.post_type,response.data.total_posts);
                }
            }
        });
    }
});

