// Appends HashOver theme CSS to page head (appendcss.js)
HashOverConstructor.prototype.appendCSS = function ()
{
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

	// Check if browser supports CSS load event
	if (css.onload !== undefined) {
		// If so, get the main HashOver element
		var mainElement = this.getMainElement ();

		// Hide the HashOver main element
		mainElement.style.display = 'none';

		// Add CSS load event listener
		css.addEventListener ('load', function () {
			// Show the HashOver main element
			mainElement.style.display = '';
		}, false);
	}

	// Append comment stylesheet link element to page head
	head.appendChild (css);
};
