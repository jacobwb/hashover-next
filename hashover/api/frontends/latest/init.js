// HashOver latest comments UI initialization process (init.js)
HashOverLatest.prototype.init = function ()
{
	// Shorthand
	var comments = this.instance.comments.primary;

	// Initial comments HTML
	var html = '';

	// Append theme CSS if enabled
	this.optionalMethod ('appendCSS', [ 'hashover-widget' ]);

	// Add main HashOver element to this HashOver instance
	this.instance['main-element'] = this.getMainElement ('hashover-widget');

	// Parse every comment
	for (var i = 0, il = comments.length; i < il; i++) {
		html += this.parseComment (comments[i]);
	}

	// Add comments to element's innerHTML
	if ('insertAdjacentHTML' in this.instance['main-element']) {
		this.instance['main-element'].insertAdjacentHTML ('beforeend', html);
	} else {
		this.instance['main-element'].innerHTML = html;
	}

	// Add control events
	for (var i = 0, il = comments.length; i < il; i++) {
		this.addControls (comments[i]);
	}
};
