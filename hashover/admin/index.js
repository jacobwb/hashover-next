document.addEventListener ('DOMContentLoaded', function () {
	// Get view links
	var viewLinks = document.getElementsByClassName ('view-link');

	// Get content frame
	var content = document.getElementById ('content');

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
	content.onload = function ()
	{
		// Remove active class from all view links
		clearViewTabs ();

		// Select active proper tab for currently loaded view
		eachViewLink (function (link) {
			var regex = new RegExp (link.getAttribute ('href'));
			var frameUrl = content.contentDocument.location.href;

			if (regex.test (decodeURIComponent (frameUrl))) {
				link.className += ' active';
			}
		});
	};
}, false);
