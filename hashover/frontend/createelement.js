// Adds properties to an element (createelement.js)
HashOverConstructor.prototype.addProperties = function (element, properties)
{
	// Do nothing if no element or properties were given
	if (!element || !properties || properties.constructor !== Object) {
		return element;
	}

	// Add each property to element
	for (var property in properties) {
		// Do nothing if property was inherited
		if (properties.hasOwnProperty (property) === false) {
			continue;
		}

		// Property value
		var value = properties[property];

		// If the property is an object add each item to existing property
		if (!!value && value.constructor === Object) {
			this.addProperties (element[property], value);
			continue;
		}

		element[property] = value;
	}

	return element;
};

// Creates an element with attributes (createelement.js)
HashOverConstructor.prototype.createElement = function (name, attr)
{
	// Create element
	var element = document.createElement (name || 'span');

	// Add properties to element
	if (attr && attr.constructor === Object) {
		element = this.addProperties (element, attr);
	}

	return element;
};
