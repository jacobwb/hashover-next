// Run all comments in array data through comments.parse function (parseall.js)
HashOver.prototype.parseAll = function (comments, element, collapse, popular)
{
	// Comments HTML
	var html = '';

	// Parse every comment
	for (var i = 0, il = comments.length; i < il; i++) {
		html += this.comments.parse (comments[i], null, collapse, popular);
	}

	// Add comments to element's innerHTML
	if ('insertAdjacentHTML' in element) {
		element.insertAdjacentHTML ('beforeend', html);
	} else {
		element.innerHTML = html;
	}

	// Add control events
	for (var i = 0, il = comments.length; i < il; i++) {
		this.addControls (comments[i]);
	}
};
