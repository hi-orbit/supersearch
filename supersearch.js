//const searchURL = 'https://supersearch.hi-orbit.com';
// const searchURL = 'https://staging.supersearch.hi-orbit.com';
const searchURL = 'http://supersearch.test:8081';

document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('message', function (event) {
        if (event.origin !== searchURL) return;
        if (event.data && event.data.is_suggestion === 'true') {
            if (searchInput.value !== event.data.data) {
                searchInput.value = event.data.data;
                searchInput.focus();
            }
        }
    });
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