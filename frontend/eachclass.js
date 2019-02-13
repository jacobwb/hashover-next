// Execute a callback for each element with a specific class (elements.js)
HashOverConstructor.prototype.eachClass = function (element, className, callback)
{
	// Get elements with a specific class name
	var elements = element.getElementsByClassName (className);

	// Execute callback for each element
	for (var i = elements.length - 1; i >= 0; i--) {
		callback (elements[i], elements, i, className);
	}
};
