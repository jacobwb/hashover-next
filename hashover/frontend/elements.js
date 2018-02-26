// Collection of convenient element functions (elements.js)
HashOverConstructor.prototype.elements = {
	cache: {},

	// Shorthand for Document.getElementById ()
	get: function (id, force, prefix)
	{
		id = (prefix !== false) ? 'hashover-' + id : id;

		if (force === true || !this.cache[id]) {
			this.cache[id] = document.getElementById (id);
		}

		return this.cache[id];
	},

	// Execute callback function if element isn't false
	exists: function (element, callback, prefix)
	{
		if (element = this.get (element, true, prefix)) {
			return callback (element);
		}

		return false;
	},

	// Adds properties to an element
	addProperties: function (element, properties)
	{
		element = element || document.createElement ('span');
		properties = properties || {};

		var value;

		// Add each property to element
		for (var property in properties) {
			if (properties.hasOwnProperty (property) === false) {
				continue;
			}

			// Property value
			value = properties[property];

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
	create: function (tagName, attributes)
	{
		tagName = tagName || 'span';
		attributes = attributes || {};

		// Create element
		var element = document.createElement (tagName);

		// Add properties to element
		element = this.addProperties (element, attributes);

		return element;
	},

	// Adds duplicate event listeners to an element
	duplicateProperties: function (element, names, value)
	{
		var properties = {};

		// Construct a properties object with duplicate values
		for (var i = 0, il = names.length; i < il; i++) {
			properties[(names[i])] = value;
		}

		// Add the properties to the object
		return this.addProperties (element, properties);
	}
};
