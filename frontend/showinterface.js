// Shows user interface (showinterface.js)
HashOver.prototype.showInterface = function (callback)
{
	// Reference to this object
	var hashover = this;

	// Elements to unhide
	var hiddenIds = [ 'form-section', 'comments-section', 'end-links' ];

	// Check if show interface link exists
	this.elementExists ('show-interface-link', function (showLink) {
		// If so, add class to hide show interface hyperlink
		hashover.classes.add (showLink, 'hashover-hide-more-link');

		// Wait for the default CSS transition
		setTimeout (function () {
			// Remove show interface hyperlink
			showLink.parentNode.removeChild (showLink);

			// Show hidden form elements
			for (var i = 0, il = hiddenIds.length; i < il; i++) {
				hashover.elementExists (hiddenIds[i], function (element) {
					element.style.display = '';
				});
			}

			// Show popular comments section
			if (hashover.setup['collapse-limit'] > 0) {
				hashover.elementExists ('popular-section', function (popularSection) {
					popularSection.style.display = '';
				});
			}

			// Execute callback
			if (typeof (callback) === 'function') {
				callback ();
			}
		}, 350);
	});
};
