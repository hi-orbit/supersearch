document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('message', function (event) {
        if (event.origin !== 'https://supersearch.hi-orbit.com') return;
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
        var id = getTrackingCookie() ?? setTrackingCookie();
        var searchQuery = encodeURIComponent(searchInput.value);
        iframeURL = ["https://supersearch.hi-orbit.com/frame?", "search_term=", searchQuery, '&id=', id, '&key=', key].join('');
        //iframeURL = ["http://supersearch.test:8081/frame?", "search_term=", searchQuery, '&id=', id, '&key=', key].join('');

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

function setTrackingCookie() {
    var trackingId = generateTrackingId();
    var daysValid = 90; // The number of days until the cookie expires
    var expiryDate = new Date();
    expiryDate.setTime(expiryDate.getTime() + (daysValid * 24 * 60 * 60 * 1000));
    var expires = "expires=" + expiryDate.toUTCString();

    document.cookie = "SuperSearchId=" + trackingId + ";" + expires + ";path=/";
    return trackingId;
}

function generateTrackingId() {
    return 'xxxx-xxxx-xxxx-xxxx'.replace(/[x]/g, function (c) {
        var r = Math.random() * 16 | 0;
        return r.toString(16);
    });
}

function getTrackingCookie() {
    var name = "SuperSearchId=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

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
