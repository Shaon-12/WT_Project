// Main script for index page

// Get query parameters for messages
function getQueryParam(param) {
    if (!window.location.search) return null;
    var search = window.location.search.substring(1);
    var vars = search.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == param) {
            return decodeURIComponent(pair[1]);
        }
    }
    return null;
}

// Display messages if any
window.onload = function () {
    var success = getQueryParam('success');
    var error = getQueryParam('error');

    if (success) {
        alert('Success: ' + success);
    } else if (error) {
        alert('Error: ' + error);
    }
};
