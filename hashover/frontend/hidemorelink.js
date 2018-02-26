// For showing more comments, via AJAX or removing a class (hidemorelink.js)
HashOver.prototype.hideMoreLink = function (finishedCallback)
{
	finishedCallback = finishedCallback || null;

	// Reference to this object
	var hashover = this;

	// Add class to hide the more hyperlink
	this.classes.add (this.instance['more-link'], 'hashover-hide-more-link');

	setTimeout (function () {
		// Remove the more hyperlink from page
		if (hashover.instance['sort-section'].contains (hashover.instance['more-link']) === true) {
			hashover.instance['sort-section'].removeChild (hashover.instance['more-link']);
		}

		// Show comment count and sort options
		hashover.elements.get ('count-wrapper').style.display = '';

		// Show popular comments section
		hashover.elements.exists ('popular-section', function (popularSection) {
			popularSection.style.display = '';
		});

		// Get each hidden comment element
		var collapsed = hashover.instance['sort-section'].getElementsByClassName ('hashover-hidden');

		// Remove hidden comment class from each comment
		for (var i = collapsed.length - 1; i >= 0; i--) {
			hashover.classes.remove (collapsed[i], 'hashover-hidden');
		}

		// Execute callback function
		if (finishedCallback !== null) {
			finishedCallback ();
		}
	}, 350);
};
