// Get main HashOver UI element (getmainelement.js)
HashOverConstructor.prototype.getMainElement = function (id)
{
	id = id || 'hashover';

	// Attempt to get main HashOver element
	var element = document.getElementById (id);

	// Check if the HashOver element exists
	if (element === null) {
		// If not, get HashOver script tag
		var script = HashOverConstructor.script;

		// Create div tag for HashOver comments to appear in
		element = this.elements.create ('div', { id: id });

		// Place the main HashOver element on the page
		script.parentNode.insertBefore (element, script);
	}

	// Add main HashOver class
	this.classes.add (element, 'hashover');

	// Check if HashOver is prepared
	if (HashOverConstructor.prepared === true) {
		// If so, add class for differentiating desktop and mobile styling
		this.classes.add (element, 'hashover-' + this.setup['device-type']);

		// And add class to indicate user login status
		if (this.setup['user-is-logged-in'] === true) {
			this.classes.add (element, 'hashover-logged-in');
		} else {
			this.classes.add (element, 'hashover-logged-out');
		}
	}

	return element;
};
