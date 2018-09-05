// Collection of convenient element functions (elements.js)
HashOverConstructor.prototype.elements = {
	// Shorthand for Document.getElementById ()
	get: function (id, asIs)
	{
		// Append pseudo-namespace prefix unless told not to
		id = (asIs === true) ? id : 'hashover-' + id;

		// Attempt to get the element by its ID
		var element = document.getElementById (id);

		return element;
	},

	// Execute callback function if element isn't false
	exists: function (id, callback, asIs)
	{
		// Attempt to get element
		var element = this.get (id, asIs);

		// Execute callback if element exists
		if (element !== null) {
			return callback (element);
		}

		return false;
	},

	// Adds properties to an element
	addProperties: function (element, properties)
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
	},

	// Creates an element with attributes
	create: function (name, attr)
	{
		// Create element
		var element = document.createElement (name || 'span');

		// Add properties to element
		if (attr && attr.constructor === Object) {
			element = this.addProperties (element, attr);
		}

		return element;
	},

	// Adds duplicate event listeners to an element
	duplicateProperties: function (element, names, value)
	{
		// Initial properties
		var properties = {};

		// Construct a properties object with duplicate values
		for (var i = 0, il = names.length; i < il; i++) {
			properties[(names[i])] = value;
		}

		// Add the properties to the object
		element = this.addProperties (element, properties);

		return element;
	},

	// Execute a callback for each element with a specific class
	eachClass: function (element, className, callback)
	{
		// Get elements with a specific class name
		var elements = element.getElementsByClassName (className);

		// Execute callback for each element
		for (var i = elements.length - 1; i >= 0; i--) {
			callback (elements[i], elements, i, className);
		}
	}
};
