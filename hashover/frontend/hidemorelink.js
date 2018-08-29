// For showing more comments, via AJAX or removing a class (hidemorelink.js)
HashOver.prototype.hideMoreLink = function (callback)
{
	// Reference to this object
	var hashover = this;

	// Sort section element
	var sortSection = this.instance['sort-section'];

	// More link element
	var moreLink = this.instance['more-link'];

	// Add class to hide the more hyperlink
	this.classes.add (this.instance['more-link'], 'hashover-hide-more-link');

	setTimeout (function () {
		// Remove the more hyperlink from page
		moreLink.parentNode.removeChild (moreLink);

		// Show comment count and sort options
		hashover.elements.get ('count-wrapper').style.display = '';

		// Show popular comments section
		hashover.elements.exists ('popular-section', function (popularSection) {
			popularSection.style.display = '';
		});

		// Get each hidden comment element
		var collapsed = sortSection.getElementsByClassName ('hashover-hidden');

		// Remove hidden comment class from each comment
		for (var i = collapsed.length - 1; i >= 0; i--) {
			hashover.classes.remove (collapsed[i], 'hashover-hidden');
		}

		// Execute callback function
		if (typeof (callback) === 'function') {
			callback ();
		}
	}, 350);
};
