window.onload = function ()
{
	// Get login dialog
	var login = document.getElementById ('login');

	// Remove red border class
	setTimeout (function () {
		login.className = login.className.replace (/red ?/, '');
	}, 1000);
};
