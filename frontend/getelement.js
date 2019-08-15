// Shorthand for `Document.getElementById` (getelement.js)
HashOverConstructor.prototype.getElement = function (id, asIs)
{
	// Prepend pseudo-namespace prefix unless told not to
	id = (asIs === true) ? id : this.prefix (id);

	// Attempt to get the element by its ID
	var element = document.getElementById (id);

	// And return element
	return element;
};

// Execute callback function if element isn't false (getelement.js)
HashOverConstructor.prototype.elementExists = function (id, callback, asIs)
{
	// Attempt to get element
	var element = this.getElement (id, asIs);

	// Execute callback if element exists
	if (element !== null) {
		return callback (element);
	}

	// Otherwise, return false
	return false;
};
