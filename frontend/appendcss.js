// Appends HashOver theme CSS to page head (appendcss.js)
HashOverConstructor.prototype.appendCSS = function (id)
{
	// Get the page head
	var head = document.head || document.getElementsByTagName ('head')[0];

	// Get head link tags
	var links = head.getElementsByTagName ('link');

	// Theme CSS regular expression
	var themeRegex = new RegExp (this.setup['theme-css']);

	// Get the main HashOver element
	var mainElement = this.getMainElement (id);

	// Do nothing if the theme StyleSheet is already in the <head>
	for (var i = 0, il = links.length; i < il; i++) {
		if (themeRegex.test (links[i].href) === true) {
			// Hide HashOver if the theme isn't loaded
			if (links[i].loaded === false) {
				mainElement.style.display = 'none';
			}

			// And do nothing else
			return;
		}
	}

	// Otherwise, create <link> element for theme StyleSheet
	var css = this.createElement ('link', {
		rel: 'stylesheet',
		href: this.setup['theme-css'],
		type: 'text/css',
		loaded: false
	});

	// Check if the browser supports CSS load events
	if (css.onload !== undefined) {
		// CSS load and error event handler
		var onLoadError = function ()
		{
			// Get all HashOver class elements
			var hashovers = document.getElementsByClassName ('hashover');

			// Show all HashOver class elements
			for (var i = 0, il = hashovers.length; i < il; i++) {
				hashovers[i].style.display = '';
			}

			// Set CSS as loaded
			css.loaded = true;
		};

		// Hide HashOver
		mainElement.style.display = 'none';

		// And and CSS load and error event listeners
		css.addEventListener ('load', onLoadError, false);
		css.addEventListener ('error', onLoadError, false);
	}

	// Append theme StyleSheet <link> element to page <head>
	head.appendChild (css);
};
