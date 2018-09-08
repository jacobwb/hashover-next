// Uncollapses the user interface (uncollapseinterface.js)
HashOver.prototype.uncollapseInterface = function (callback)
{
	// Reference to this object
	var hashover = this;

	// Elements to unhide
	var uncollapseIDs = [ 'form-section', 'comments-section', 'end-links' ];

	// Check if the uncollapse interface link exists
	this.elementExists ('uncollapse-interface-link', function (uncollapseLink) {
		// If so, add class to hide the uncollapse interface hyperlink
		hashover.classes.add (uncollapseLink, 'hashover-hide-more-link');

		// Wait for the default CSS transition
		setTimeout (function () {
			// Remove the uncollapse interface hyperlink from page
			uncollapseLink.parentNode.removeChild (uncollapseLink);

			// Show hidden form elements
			for (var i = 0, il = uncollapseIDs.length; i < il; i++) {
				hashover.elementExists (uncollapseIDs[i], function (element) {
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
