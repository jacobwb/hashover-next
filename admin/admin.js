// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get logo
	var logo = document.getElementById ('logo');

	// Get view links
	var viewLinks = document.getElementsByClassName ('view-link');

	// Execute a given function for each view link
	function eachViewLink (callback)
	{
		for (var i = 0, il = viewLinks.length; i < il; i++) {
			callback (viewLinks[i]);
		}
	}

	// Remove active class from all view links
	function clearViewTabs ()
	{
		eachViewLink (function (link) {
			link.className = 'view-link';
		});
	}

	// Automatically select the proper view tab on page load
	window.onload = function ()
	{
		// Set logo height to auto
		logo.style.height = 'auto';

		// Remove active class from all view links
		clearViewTabs ();

		// Select active proper tab for currently loaded view
		eachViewLink (function (link) {
			var regex = new RegExp (link.getAttribute ('href'));
			var frameUrl = window.location.href;

			if (regex.test (decodeURIComponent (frameUrl))) {
				link.className += ' active';
			}
		});
	};
}, false);
