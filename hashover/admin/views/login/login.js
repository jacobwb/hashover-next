// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get login dialog
	var login = document.getElementById ('login');

	// Remove red border class
	setTimeout (function () {
		login.className = login.className.replace (/red ?/, '');
	}, 1000);
}, false);
