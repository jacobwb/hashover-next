// Get URL from canonical link element (geturl.js)
HashOverConstructor.getCanonical = function ()
{
	// Check if we have query selector support
	if (typeof (document.querySelector) === 'function') {
		// If so, get the canonical link element
		var canonical = document.querySelector ('link[rel="canonical"]');

		// Return canonical link element URL is one was found
		if (canonical !== null && canonical.href) {
			return canonical.href;
		}
	}

	// Otherwise, get document head element
	var head = document.head || document.getElementsByTagName ('head')[0];

	// Get link elements in document head
	var links = head.getElementsByTagName ('link');

	// Run through link elements
	for (var i = 0, il = links.length; i < il; i++) {
		// Return canonical link element URL is one was found
		if (links[i].rel === 'canonical' && links[i].href) {
			return links[i].href;
		}
	}

	// Otherwise, return actual page URL
	return window.location.href.split ('#')[0];
};

// Get actual page URL or canonical URL (geturl.js)
HashOverConstructor.getURL = function (canonical)
{
	// Return actual page URL if told to
	if (canonical === false) {
		return window.location.href.split ('#')[0];
	}

	// Otherwise, return canonical URL
	return HashOverConstructor.getCanonical ();
};
