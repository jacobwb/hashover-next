// HashOver latest comments UI initialization process (init.js)
HashOverLatest.prototype.init = function ()
{
	// Shorthand
	var comments = this.instance.comments.primary;

	// Initial comments HTML
	var html = '';

	// Get the main HashOver element
	var mainElement = this.getMainElement ('hashover-latest');

	// Append theme CSS if enabled
	this.optionalMethod ('appendCSS', [ 'hashover-latest' ]);

	// Add main HashOver element to this HashOver instance
	this.instance['main-element'] = mainElement;

	// Parse every comment
	for (var i = 0, il = comments.length; i < il; i++) {
		html += this.parseComment (comments[i]);
	}

	// Check if we can insert HTML adjacently
	if ('insertAdjacentHTML' in mainElement) {
		// If so, insert comments adjacently
		mainElement.insertAdjacentHTML ('beforeend', html);
	} else {
		// If not, add comments as element's inner HTML
		mainElement.innerHTML = html;
	}

	// Add control events
	for (var i = 0, il = comments.length; i < il; i++) {
		this.addControls (comments[i]);
	}
};
