// Appends HashOver theme CSS to page head (appendcss.js)
HashOverConstructor.prototype.appendCSS = function (id)
{
	id = id || 'hashover';

	// Get the page head
	var head = document.head || document.getElementsByTagName ('head')[0];

	// Get head link tags
	var links = head.getElementsByTagName ('link');

	// Theme CSS regular expression
	var themeRegex = new RegExp (this.setup['theme-css']);

	// No nothing if theme stylesheet is already in the page head
	for (var i = 0, il = links.length; i < il; i++) {
		if (themeRegex.test (links[i].href) === true) {
			return;
		}
	}

	// Create link element for comment stylesheet
	var css = this.elements.create ('link', {
		rel: 'stylesheet',
		href: this.setup['theme-css'],
		type: 'text/css'
	});

	// Load and error event listener
	function onLoadError ()
	{
		// Show the HashOver main element
		mainElement.style.display = '';
	}

	// Check if browser supports CSS load event
	if (css.onload !== undefined) {
		// If so, get the main HashOver element
		var mainElement = this.getMainElement (id);

		// Hide the HashOver main element
		mainElement.style.display = 'none';

		// Add CSS load and error event listeners
		css.addEventListener ('load', onLoadError, false);
		css.addEventListener ('error', onLoadError, false);
	}

	// Append comment stylesheet link element to page head
	head.appendChild (css);
};
