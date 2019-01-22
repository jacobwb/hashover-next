// Adds duplicate event listeners to an element (duplicateproperties.js)
HashOverConstructor.prototype.duplicateProperties = function (element, names, value)
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
};
