// Get either the actual page URL or the declared canonical URL (geturl.js)
HashOverConstructor.getURL = function (canonical)
{
	canonical = (canonical !== false);

	// Get the actual page URL
	var url = window.location.href.split ('#')[0];

	// Return the actual page URL if told to
	if (canonical === false) {
		return url;
	}

	// Otherwise, return the declared canonical URL if available
	if (typeof (document.querySelector) === 'function') {
		var canonical = document.querySelector ('link[rel="canonical"]');

		if (canonical !== null && canonical.href !== undefined) {
			url = canonical.href;
		}
	} else {
		// Fallback for old web browsers without querySelector
		var head = document.head || document.getElementsByTagName ('head')[0];
		var links = head.getElementsByTagName ('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].rel === 'canonical') {
				url = links[i].href;
				break;
			}
		}
	}

	return url;
};
