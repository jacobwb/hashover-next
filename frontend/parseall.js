// Parse all comments in a given array (parseall.js)
HashOver.prototype.parseAll = function (comments, element, collapse, popular)
{
	// Comments HTML
	var html = '';

	// Parse every comment
	for (var i = 0, il = comments.length; i < il; i++) {
		html += this.parseComment (comments[i], null, collapse, popular);
	}

	// HTML parsing start time
	var htmlStart = Date.now ();

	// Check if we can insert HTML adjacently
	if ('insertAdjacentHTML' in element) {
		// If so, remove all existing content
		element.textContent = '';

		// And insert HTML adjacently
		element.insertAdjacentHTML ('beforeend', html);
	} else {
		// If not, add comments as element's inner HTML
		element.innerHTML = html;
	}

	// Get HTML parsing time
	var htmlTime = Date.now () - htmlStart;

	// Add control events
	for (var i = 0, il = comments.length; i < il; i++) {
		this.addControls (comments[i]);
	}

	return htmlTime;
};
