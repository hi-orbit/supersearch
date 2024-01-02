document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('supersearch-input');
    var key = document.getElementById('supersearch-key');

    window.addEventListener('message', function(event) {
        if (event.origin !== 'https://supersearch.hi-orbit.com') return;
        if (event.data && event.data.is_suggestion === 'true') {
            if (searchInput.value !== event.data.data) {
                searchInput.value = event.data.data;
                searchInput.focus();
            }
        }
    });

    searchInput.addEventListener('keyup', function () {
        if (searchInput.value.trim() === '') {
            $.featherlight.current().close();
        } else {
            var searchInputPosition = $('#supersearch-input').offset();
            $('.featherlight-iframe').css('top', searchInputPosition.top + 8 + 'px');

            var id = getTrackingCookie() ?? setTrackingCookie();
            var searchQuery = encodeURIComponent(searchInput.value);
            iframeURL = ["https://supersearch.hi-orbit.com/frame?", "search_term=", searchQuery, '&id=', id, '&key=', key.value].join('');

            if ($.featherlight.current()) {
                $.featherlight.current().$instance.find('iframe').attr('src', iframeURL);
            } else {
                $.featherlight({
                    closeOnClick: false,
                    closeOnEsc: false,
                    closeIcon: '',
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
                });
            }
            searchInput.focus();
        }
    });

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
        return 'xxxx-xxxx-xxxx-xxxx'.replace(/[x]/g, function(c) {
            var r = Math.random() * 16 | 0;
            return r.toString(16);
        });
    }

    function getTrackingCookie() {
        var name = "SuperSearchId=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i < ca.length; i++) {
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
    
});

function repositionFeatherlight(instance) {
    var searchInputPosition = $('#supersearch-input').offset();
    instance.css('top', searchInputPosition.top + 40 + 'px');
}