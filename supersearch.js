/**
 * WordPress plugin "SuperSearch" admin javascript file.
 * Copyright (C) 2024, Hi-Orbit
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
const SUPERSEARCH_SEARCH_URL = 'https://supersearch.hi-orbit.com';

document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('message', function (event) {
        if (event.origin !== SUPERSEARCH_SEARCH_URL) return;
        if (event.data && event.data.is_suggestion === 'true') {
            var searchInput = document.getElementById('supersearch-input');
            if (searchInput.value !== event.data.data) {
                searchInput.value = event.data.data;
                searchInput.focus();
            }
        }
    });
    jQuery(document).on('click', function (e) {
        if (jQuery(e.target).closest('.featherlight').length === 0) {
            jQuery.featherlight.close();
        }
    });
    document.getElementById('supersearch-id').value = supersearch_get_create_cookie();
});
var supersearch_process_search = supersearch_debounce(function (searchInput) {
    if (searchInput.value.trim() === '') {
        if (null !== jQuery.featherlight.current()) {
            jQuery.featherlight.current().close();
        }
    } else {
        var searchQuery = encodeURIComponent(searchInput.value);
        var key = document.getElementById('supersearch-key').value;
        var tracking_id = document.getElementById('supersearch-id').value;
        iframeURL = [SUPERSEARCH_SEARCH_URL + '/frame?', "search_term=", searchQuery, '&id=', tracking_id, '&key=', key].join('');
        if (jQuery.featherlight.current()) {
            jQuery.featherlight.current().$instance.find('iframe').attr('src', iframeURL);
        } else {
            jQuery.featherlight({
                closeOnClick: true,
                closeOnEsc: true,
                closeIcon: '&#10005;',
                loading: '<div class="super-search-loader"></div>',
                iframe: iframeURL,
                afterOpen: function (event) {
                    var searchInputPosition = jQuery('#supersearch-input').offset();
                    this.$instance.find('.featherlight-iframe').css('top', searchInputPosition.top + 8 + 'px');
                    supersearch_reposition_featherlight(this.$instance);
                },
                beforeOpen: function (event) {
                    var searchInputPosition = jQuery('#supersearch-input').offset();
                    this.$instance.find('.featherlight-iframe').css('top', searchInputPosition.top + 8 + 'px');
                    supersearch_reposition_featherlight(this.$instance);
                },
                beforeClose: function (event) {
                    searchInput.value = '';
                }
            });
        }
        searchInput.focus();
    }
}, 300);
function search_query(searchInput) {
    supersearch_process_search(searchInput);
}
function supersearch_debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
function supersearch_reposition_featherlight(instance) {
    if (null === instance) {
        return;
    }
    var searchInputPosition = jQuery('#supersearch-input').offset();
    if (searchInputPosition.top == 0) {
        var mobile_top_offset = parseInt(document.getElementById('mobile_top_offset').value);
        var mobile_top_offset = searchInputPosition.top + mobile_top_offset;
        instance.css('top', mobile_top_offset + 'px');
    } else {
        var mobile_top_offset = parseInt(document.getElementById('desktop_top_offset').value);
        instance.css('top', searchInputPosition.top + mobile_top_offset + 'px');
    }
}
function supersearch_get_create_cookie() {
    function generateGUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    function getCookieValue() {
        var matches = document.cookie.match('(^|;)\\s*_super_search_id\\s*=\\s*([^;]+)');
        return matches ? decodeURIComponent(matches[2]) : null;
    }
    var cookieValue = getCookieValue();
    if (!cookieValue) {
        cookieValue = generateGUID();
        var date = new Date();
        date.setTime(date.getTime() + (90 * 24 * 60 * 60 * 1000)); // Set expiration date 90 days from now
        var expires = "expires=" + date.toUTCString();
        document.cookie = "_super_search_id=" + encodeURIComponent(cookieValue) + ";" + expires + ";path=/";
    }
    return cookieValue;
}