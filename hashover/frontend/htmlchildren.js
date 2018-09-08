// Converts an HTML string to DOM elements (htmlchildren.js)
HashOver.prototype.htmlChildren = function (html)
{
	// Create a div to place the HTML into for parsing
	var div = this.createElement ('div', {
		innerHTML: html
	});

	// Return the child elements
	return div.children;
};
