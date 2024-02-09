const searchURL = 'https://supersearch.hi-orbit.com';

document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('message', function (event) {
        if (event.origin !== searchURL) return;
        if (event.data && event.data.is_suggestion === 'true') {
            var searchInput = document.getElementById('supersearch-input');
            if (searchInput.value !== event.data.data) {
                searchInput.value = event.data.data;
                searchInput.focus();
            }
        }
    });
    $(document).on('click', function(e) {
        if ($(e.target).closest('.featherlight').length === 0) {
            $.featherlight.close();
        }
    });
    document.getElementById('supersearch-id').value = getOrCreateCookie();
});

function search_query(searchInput) {
    if (searchInput.value.trim() === '') {
        if (null !== $.featherlight.current()) {
            $.featherlight.current().close();
        }
    } else {
        var searchInputPosition = $('#supersearch-input').offset();
        $('.featherlight-iframe').css('top', searchInputPosition.top + 8 + 'px');
        var key = document.getElementById('supersearch-key').value;
        var searchQuery = encodeURIComponent(searchInput.value);
        var tracking_id = document.getElementById('supersearch-id').value;
        iframeURL = [searchURL + '/frame?', "search_term=", searchQuery, '&id=', tracking_id, '&key=', key].join('');
        if ($.featherlight.current()) {
            $.featherlight.current().$instance.find('iframe').attr('src', iframeURL);
        } else {
            $.featherlight({
                closeOnClick: true,
                closeOnEsc: true,
                closeIcon: '&#10005;',
                loading: '<div class="super-search-loader"></div>',
                iframe: iframeURL,
                onKeyUp: function () {
                    repositionFeatherlight(this.$instance);
                },
                beforeOpen: function (event) {
                    repositionFeatherlight(this.$instance);
                },
                afterOpen: function (event) {
                    repositionFeatherlight(this.$instance);
                },
                beforeClose: function (event) {
                    searchInput.value = '';
                }
            });
        }
        searchInput.focus();
    }
};

function repositionFeatherlight(instance) {
    var searchInputPosition = $('#supersearch-input').offset();
    if (searchInputPosition.top == 0){
        var mobile_top_offset = parseInt(document.getElementById('mobile_top_offset').value);
        var mobile_top_offset =  searchInputPosition.top + mobile_top_offset;
        instance.css('top', mobile_top_offset + 'px');
    } else {
        var mobile_top_offset = parseInt(document.getElementById('desktop_top_offset').value);
        instance.css('top', searchInputPosition.top + mobile_top_offset + 'px');
    }
}

function getOrCreateCookie() {
    function generateGUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
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