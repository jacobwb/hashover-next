// Get main HashOver UI element (getmainelement.js)
HashOverConstructor.prototype.getMainElement = function (id)
{
	// Given element ID or default
	id = id || this.prefix ();

	// Attempt to get main HashOver element
	var element = document.getElementById (id);

	// Check if the HashOver element exists
	if (element === null) {
		// If not, get script tag
		var script = this.constructor.script;

		// Create div for comments to appear in
		element = this.createElement ('div', { id: id });

		// Check if script tag is in the body
		if (document.body.contains (script) === true) {
			// If so, place HashOver element before script tag
			script.parentNode.insertBefore (element, script);
		} else {
			// If not, place HashOver element in the body
			document.body.appendChild (element);
		}
	}

	// Add main HashOver class
	this.classes.add (element, 'hashover');

	// Check if backend is ready
	if (this.constructor.backendReady === true) {
		// If so, add class indictating desktop or mobile styling
		this.classes.add (element, 'hashover-' + this.setup['device-type']);

		// Add class for raster or vector images
		if (this.setup['image-format'] === 'svg') {
			this.classes.add (element, 'hashover-vector');
		} else {
			this.classes.add (element, 'hashover-raster');
		}

		// And add class to indicate user login status
		if (this.setup['user-is-logged-in'] === true) {
			this.classes.add (element, 'hashover-logged-in');
		} else {
			this.classes.add (element, 'hashover-logged-out');
		}
	}

	return element;
};
